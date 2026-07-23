<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OcrController extends Controller
{
    private const GEMINI_MODEL = 'gemini-3.6-flash';

    private const LABEL_ARTIFACTS = [
        'last name', 'first name', 'middle name', 'sex', 'gender',
        'date of birth', 'birth date', 'dob', 'address', 'aduress',
        'license no', 'license number', 'nationality', 'apelyido', 'pangalan',
    ];

    /**
     * Handle the incoming scan request using Smart Fallback OCR
     */
    public function scanId(Request $request)
    {
        $request->validate([
            'image' => 'required|string'
        ]);

        $imageBase64 = $request->input('image');

        // Remove data URI prefix if present
        if (preg_match('/^data:image\/(\w+);base64,/', $imageBase64, $type)) {
            $imageBase64 = substr($imageBase64, strpos($imageBase64, ',') + 1);
        }

        // Tier 1: Try Gemini
        try {
            $parsedData = $this->scanWithGemini($imageBase64);
            if ($parsedData) {
                return response()->json([
                    'success' => true,
                    'source' => 'gemini',
                    'data' => $parsedData
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Gemini OCR failed: ' . $e->getMessage());
        }

        // Tier 2: Try OCR.Space
        try {
            $parsedData = $this->scanWithOcrSpace($imageBase64);
            if ($parsedData) {
                return response()->json([
                    'success' => true,
                    'source' => 'ocrspace',
                    'data' => $parsedData
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('OCR.Space failed: ' . $e->getMessage());
        }

        // Tier 3: Return failure so client falls back to local Tesseract
        return response()->json([
            'success' => false,
            'message' => 'Cloud OCR failed. Falling back to local scanner.'
        ], 500);
    }

    /**
     * Shared instructions/JSON schema for extracting motorist fields from a
     * Philippine driver's license. Reused by both the vision (Tier 1) and
     * OCR.Space-text (Tier 2) Gemini calls so there is one parsing brain
     * instead of two implementations that can drift out of sync.
     */
    private function buildExtractionInstructions($fromRawOcrText = false)
    {
        $instructions = "You are an expert OCR AI specializing in Philippine Driver's Licenses. Extract the TRUE personal details of the cardholder"
                . ($fromRawOcrText ? " from the raw OCR text provided below.\n\n" : " from the provided image.\n\n")
                . "CRITICAL INSTRUCTIONS:\n"
                . "1. DO NOT extract the printed field labels (e.g. 'Last Name', 'First Name', 'Address', 'License No', 'Date of Birth', 'Sex'). Look for the actual personal data written near or below these labels.\n"
                . "2. Name Format: On a PH Driver's License, the name is printed immediately BELOW the label 'Last Name, First Name, Middle Name'. For example, if it says 'CALIDA, KRIS IAN SAPOTALO', the last_name is 'CALIDA', the first_name is 'KRIS IAN', and the middle_name is 'SAPOTALO'.\n"
                . "3. License No: Look below the label 'License No', usually formatted like 'G25-24-005686' or similar.\n"
                . "4. Date of Birth: Look below the label 'Date of Birth' (Format: YYYY/MM/DD). You MUST return it as YYYY-MM-DD.\n"
                . "5. Address: Look below the label 'Address'. It is usually a full address like 'SAN JUAN, TUBURAN, CEBU, 6043'.\n"
                . "6. Gender: Look below 'Sex'. 'M' means Male, 'F' means Female.\n";

        if ($fromRawOcrText) {
            $instructions .= "7. This text came from a generic OCR engine, not a vision model, so layout is lossy: a label and its value are usually on two separate whole lines, in that order. Some rows are a MERGED multi-column header naming several fields at once (e.g. 'Nationality Sex Date of Birth Weight(kg) Height(m)') immediately followed by ONE values line with the corresponding values in the same left-to-right order (e.g. 'PHL M 2001/10/05 58 1.64'). When you see this pattern, match each value to its column by position.\n";
        }

        $instructions .= "\nReturn ONLY a pure JSON object, without any markdown formatting or backticks. If a field cannot be found, return an empty string. The JSON must exactly match these keys:\n"
                . "- first_name\n"
                . "- last_name\n"
                . "- middle_name\n"
                . "- license_number\n"
                . "- date_of_birth\n"
                . "- gender\n"
                . "- address";

        return $instructions;
    }

    private function scanWithGemini($base64Image)
    {
        $prompt = $this->buildExtractionInstructions(false);

        return $this->callGemini([
            ['text' => $prompt],
            [
                'inlineData' => [
                    'mimeType' => 'image/jpeg',
                    'data' => $base64Image
                ]
            ]
        ]);
    }

    /**
     * Tier 2's primary parser: reuses the exact same extraction brain as
     * Tier 1, just fed OCR.Space's raw text instead of an image.
     */
    private function extractFieldsWithGeminiText($rawText)
    {
        $prompt = $this->buildExtractionInstructions(true)
                . "\n\nRaw OCR text to extract from:\n---\n" . $rawText . "\n---";

        return $this->callGemini([
            ['text' => $prompt]
        ]);
    }

    /**
     * Shared Gemini call: HTTP request, response unwrapping, JSON parsing,
     * and field validation. Used by both the vision call (Tier 1) and the
     * text-only call (Tier 2's primary parser).
     */
    private function callGemini(array $parts)
    {
        $apiKey = config('services.gemini.key');
        if (!$apiKey) {
            throw new \Exception('Gemini API key not configured.');
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . self::GEMINI_MODEL . ':generateContent?key=' . $apiKey;

        $response = Http::post($url, [
            'contents' => [
                [
                    'parts' => $parts
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.0,
                'responseMimeType' => 'application/json',
            ]
        ]);

        if (!$response->successful()) {
            throw new \Exception('HTTP Error: ' . $response->body());
        }

        $json = $response->json();

        $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (empty($text)) {
            throw new \Exception('Gemini returned empty response.');
        }

        // Strip markdown if it somehow bypassed responseMimeType
        $text = trim($text);
        if (str_starts_with($text, '```json')) {
            $text = substr($text, 7);
        }
        if (str_ends_with($text, '```')) {
            $text = substr($text, 0, -3);
        }

        $data = json_decode(trim($text), true);
        if (!$data || !is_array($data)) {
            throw new \Exception('Failed to parse Gemini JSON: ' . $text);
        }

        // If at least one crucial field is found, consider it a success
        if (empty($data['first_name']) && empty($data['last_name']) && empty($data['license_number'])) {
            // TEMP DEBUG (see plan: the-still-cant-extract-snazzy-parnas): log the raw
            // parsed payload so we can tell "Gemini genuinely saw nothing" apart from
            // "Gemini returned data, just not in these 3 checked fields" or label junk
            // that got silently dropped elsewhere. Remove once root cause is confirmed.
            Log::debug('Gemini OCR raw parsed payload (no relevant data path): ' . json_encode($data));
            throw new \Exception('Gemini found no relevant data.');
        }

        // Validate BEFORE normalizing gender — normalizeGender() would
        // otherwise silently rewrite a leaked "Sex" label into "Other",
        // hiding exactly the failure this guard exists to catch.
        $this->assertNoLabelArtifacts($data);

        if (isset($data['gender'])) {
            $data['gender'] = $this->normalizeGender($data['gender']);
        }

        return $data;
    }

    /**
     * Map free-form gender text (M, male, F, Babae, ...) to the form's
     * exact option values so the <select> actually ends up pre-selected.
     */
    private function normalizeGender($value)
    {
        $value = trim((string) $value);
        if (preg_match('/^m(ale)?$/i', $value)) {
            return 'Male';
        }
        if (preg_match('/^f(emale)?$/i', $value)) {
            return 'Female';
        }
        return $value !== '' ? 'Other' : '';
    }

    /**
     * Guards against extractors that latch onto the printed field label
     * itself instead of its value (e.g. an OCR/positional mismatch handing
     * back "Sex" or "Address" verbatim) — this is silently wrong data, not
     * "found nothing", so it must fail the tier rather than be saved.
     */
    private function assertNoLabelArtifacts(array $data): void
    {
        foreach (['first_name', 'last_name', 'gender', 'date_of_birth', 'address'] as $field) {
            $value = strtolower(trim((string) ($data[$field] ?? '')));
            if ($value !== '' && in_array($value, self::LABEL_ARTIFACTS, true)) {
                throw new \Exception("Extraction returned a label artifact for '{$field}': " . $data[$field]);
            }
        }
    }

    /**
     * A real gender value is always a single short token (M, F, Male,
     * Female). Anything else — including a whole trailing label swallowed by
     * a greedy same-line match, or a misaligned column value — is rejected
     * rather than silently normalized to "Other" by normalizeGender().
     */
    private function isPlausibleGenderToken($value): bool
    {
        return preg_match('/^(m|f|male|female)$/i', trim((string) $value)) === 1;
    }

    /**
     * Catches a neighboring field label leaking into a captured value without
     * being an exact match for it (e.g. "M Date of Birth 2001/10/05" from a
     * merged line, or a real address that accidentally swallowed the next
     * label) — assertNoLabelArtifacts() only rejects an exact label string,
     * this rejects a label appearing anywhere inside the value.
     */
    private function looksContaminatedByLabel($value): bool
    {
        return preg_match(
            '/\b(last\s*name|first\s*name|middle\s*name|sex|gender|date\s*of\s*birth|birth\s*date|license\s*no|nationality|weight|height)\b/i',
            (string) $value
        ) === 1;
    }

    private function scanWithOcrSpace($base64Image)
    {
        $apiKey = config('services.ocrspace.key');
        if (!$apiKey) {
            throw new \Exception('OCR.Space API key not configured.');
        }

        $response = Http::asForm()->post('https://api.ocr.space/parse/image', [
            'apikey' => $apiKey,
            'base64Image' => 'data:image/jpeg;base64,' . $base64Image,
            'language' => 'eng',
            'isOverlayRequired' => 'false',
            'OCREngine' => '2', // Engine 2 is better for numbers/special chars
        ]);

        if (!$response->successful()) {
            throw new \Exception('HTTP Error: ' . $response->body());
        }

        $json = $response->json();
        if (isset($json['IsErroredOnProcessing']) && $json['IsErroredOnProcessing'] == true) {
            // TEMP DEBUG (see plan: the-still-cant-extract-snazzy-parnas): OCR.Space's
            // free tier caps requests at 1MB; log the actual payload size so the next
            // failure tells us whether this is a size-limit hit vs. a genuine engine error.
            Log::debug('OCR.Space error payload size: base64 length=' . strlen($base64Image) . ' bytes, decoded approx=' . (int) (strlen($base64Image) * 3 / 4) . ' bytes');
            throw new \Exception('OCR.Space error: ' . ($json['ErrorMessage'][0] ?? 'Unknown'));
        }

        $parsedText = $json['ParsedResults'][0]['ParsedText'] ?? '';
        if (empty($parsedText)) {
            throw new \Exception('OCR.Space returned empty text.');
        }

        // Primary parser: reuse the same Gemini extraction brain as Tier 1.
        // Only drop to the regex/positional safety net below if that call
        // itself fails (no key, quota exhausted, outage, bad JSON) — this
        // keeps Tier 2 usable even when Gemini is fully unavailable, which
        // is the exact scenario Tier 2 exists for.
        try {
            return $this->extractFieldsWithGeminiText($parsedText);
        } catch (\Exception $e) {
            Log::warning('OCR.Space text->Gemini extraction failed, using regex fallback: ' . $e->getMessage());
            return $this->parseRawText($parsedText);
        }
    }

    /**
     * Regex/positional safety net used only when extractFieldsWithGeminiText()
     * itself failed. Deliberately conservative: a field is only filled when a
     * reasonably confident match is found.
     */
    private function parseRawText($text)
    {
        $lines = array_values(array_filter(
            array_map('trim', explode("\n", $text)),
            fn($line) => $line !== ''
        ));

        $data = [
            'license_number' => '',
            'first_name' => '',
            'last_name' => '',
            'middle_name' => '',
            'date_of_birth' => '',
            'gender' => '',
            'address' => '',
        ];

        $fullText = implode(' ', $lines);

        // Find License Number (Format: N01-23-456789 or similar)
        if (preg_match('/[A-Z]\d{2}-\d{2}-\d{6}/', $fullText, $matches)) {
            $data['license_number'] = $matches[0];
        }

        foreach ($lines as $i => $line) {
            // Combined "Last Name, First Name, Middle Name" label line -> the
            // next line holds "SURNAME, GIVEN NAMES LASTWORD" as one string.
            if (
                $data['last_name'] === ''
                && preg_match('/Last\s*Name.*First\s*Name.*Middle\s*Name|Apelyido.*Pangalan/i', $line)
                && isset($lines[$i + 1])
            ) {
                $nameLine = $lines[$i + 1];
                $commaPos = strpos($nameLine, ',');
                if ($commaPos !== false) {
                    $data['last_name'] = trim(substr($nameLine, 0, $commaPos));
                    $restParts = preg_split('/\s+/', trim(substr($nameLine, $commaPos + 1)), -1, PREG_SPLIT_NO_EMPTY);
                    if (count($restParts) > 1) {
                        $data['middle_name'] = array_pop($restParts);
                        $data['first_name'] = implode(' ', $restParts);
                    } elseif (count($restParts) === 1) {
                        $data['first_name'] = $restParts[0];
                    }
                }
            }

            // Standalone "Sex"/"Gender" label, same-line value (some ID types
            // print this on one line rather than in a merged column row).
            // Captures only the first token — not the rest of the line — so
            // a merged line like "Sex M Date of Birth 2001/10/05" can't drag
            // the trailing label into the gender value.
            if (
                $data['gender'] === ''
                && preg_match('/^(Sex|Gender)\s*[:\-]?\s*(\S+)/i', $line, $m)
                && $this->isPlausibleGenderToken($m[2])
            ) {
                $data['gender'] = trim($m[2]);
            }

            // Standalone "Date of Birth" label, same-line or next-line value.
            if (
                $data['date_of_birth'] === ''
                && preg_match('/^(Date\s*of\s*Birth|Birth\s*Date)\s*[:\-]?\s*(.*)$/i', $line, $m)
            ) {
                $dobRaw = trim($m[2]) !== '' ? trim($m[2]) : ($lines[$i + 1] ?? '');
                if (preg_match('#(\d{4})/(\d{1,2})/(\d{1,2})#', $dobRaw, $dm)) {
                    $data['date_of_birth'] = sprintf('%04d-%02d-%02d', $dm[1], $dm[2], $dm[3]);
                }
            }

            // Standalone "Address" (or OCR-garbled "Aduress") label.
            if (
                $data['address'] === ''
                && preg_match('/^(Address|Aduress)\s*[:\-]?\s*(.*)$/i', $line, $m)
            ) {
                $addr = trim($m[2]) !== '' ? trim($m[2]) : ($lines[$i + 1] ?? '');
                if ($addr !== '') {
                    $data['address'] = $addr;
                }
            }

            // Merged multi-column header row, e.g.
            // "Nationality Sex Date of Birth Weight(kg) Height(m)" followed
            // by a values row like "PHL M 2001/10/05 58 1.64". Match each
            // header to the value at the same ORDINAL position, not by
            // character offset, since "Date of Birth" is three words wide
            // but only one value token wide.
            if (isset($lines[$i + 1]) && preg_match_all(
                '/Nationality|Sex|Date\s+of\s+Birth|Weight(?:\s*\(kg\))?|Height(?:\s*\(m\))?/i',
                $line,
                $colMatches
            ) && count($colMatches[0]) >= 2) {
                $columns = array_map(
                    fn($c) => strtolower(preg_replace('/\s+/', ' ', trim($c))),
                    $colMatches[0]
                );
                $values = preg_split('/\s+/', trim($lines[$i + 1]), -1, PREG_SPLIT_NO_EMPTY);

                if (count($values) >= count($columns)) {
                    foreach ($columns as $idx => $col) {
                        $val = $values[$idx] ?? '';
                        if ($val === '') {
                            continue;
                        }
                        if ($data['gender'] === '' && str_contains($col, 'sex') && $this->isPlausibleGenderToken($val)) {
                            $data['gender'] = $val;
                        }
                        if ($data['date_of_birth'] === '' && str_contains($col, 'date of birth')) {
                            if (preg_match('#(\d{4})/(\d{1,2})/(\d{1,2})#', $val, $dm)) {
                                $data['date_of_birth'] = sprintf('%04d-%02d-%02d', $dm[1], $dm[2], $dm[3]);
                            }
                        }
                    }
                }
            }
        }

        // Validate BEFORE normalizing gender, same reasoning as callGemini().
        $this->assertNoLabelArtifacts($data);

        if ($data['gender'] !== '') {
            $data['gender'] = $this->normalizeGender($data['gender']);
        }

        return $data;
    }
}
