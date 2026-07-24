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
        'blood type', 'height', 'weight', 'license type', 'conditions',
        'restrictions', 'expiration date', 'agency code', 'date issued',
    ];

    private const VALID_BLOOD_TYPES = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];

    private const EXTRACTED_FIELDS = [
        'first_name', 'last_name', 'middle_name', 'license_number', 'date_of_birth',
        'gender', 'address', 'place_of_birth', 'license_expiry_date', 'license_issued_date',
        'license_type', 'blood_type', 'height', 'weight', 'license_conditions',
        'license_restriction',
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
                . "CRITICAL INSTRUCTIONS:\n";

        if ($fromRawOcrText) {
            $instructions .= "1. DO NOT extract the printed field labels (e.g. 'Last Name', 'First Name', 'Address', 'License No', 'Date of Birth', 'Sex'). Look for the actual personal data written near or below these labels.\n";
        } else {
            $instructions .= "1. THE MOST IMPORTANT RULE: on this card, printed field LABELS (e.g. 'Last Name', 'First Name', 'Sex', 'Date of Birth', 'Address', 'License No', 'DL Codes') are printed in a SMALL, thin, regular-weight font. The actual VALUE for every field is printed in BOLD, LARGER, ALL-CAPS text, positioned directly below or beside its label.\n"
                    . "2. DO NOT extract printed field labels themselves under any circumstances.\n"
                    . "EXAMPLE: if caption is 'Last Name, First Name, Middle Name' and bold line reads 'CALIDA, KRIS IAN SAPOTALO', return last_name='CALIDA', first_name='KRIS IAN', middle_name='SAPOTALO'.\n";
        }

        $instructions .= "3. Name Format: 'CALIDA, KRIS IAN SAPOTALO' -> last_name='CALIDA', first_name='KRIS IAN', middle_name='SAPOTALO'.\n"
                . "4. License No: Look below/beside 'License No' or 'License No.' (e.g. 'G25-24-005686').\n"
                . "5. Date of Birth: Look below/beside 'Date of Birth' (Format: YYYY/MM/DD). Return as YYYY-MM-DD.\n"
                . "6. Address: Look below/beside 'Address' (e.g. 'SAN JUAN, TUBURAN, CEBU, 6043').\n"
                . "7. Place of Birth: Use explicit place of birth if present; otherwise use the address or city/province.\n"
                . "8. Gender: Look below 'Sex'. 'M' means Male, 'F' means Female.\n"
                . "9. License Expiry Date: Look below 'Expiration Date' or 'Valid Until' (Format: YYYY/MM/DD). Return as YYYY-MM-DD.\n"
                . "10. License Issued Date: Issue/renewal date if separate from expiration date (YYYY-MM-DD) or empty string.\n"
                . "11. License Type: 'PROFESSIONAL DRIVER'S LICENSE' -> 'Professional'; 'DRIVER'S LICENSE' -> 'Non-Professional'.\n"
                . "12. Blood Type: Must be one of: O+, O-, A+, A-, B+, B-, AB+, AB-. If none, return empty string.\n"
                . "13. Height: In meters (e.g. '1.64'), convert to centimeters (e.g. '164'). If already in cm, return number as string.\n"
                . "14. Weight: In kilograms (e.g. '58'). Return whole number string.\n"
                . "15. Restriction Codes / DL Codes: Look below/beside 'DL Codes' or 'Restrictions' (e.g. 'A,A1', 'B, B1, B2' or numeric '1, 2'). Map numeric restriction codes to DL Codes: 1->A,A1; 2->B,B1,B2; 3->C; 4->D; 5->BE; 6->A,A1; 7->CE; 8->CE. Return comma-separated DL codes like 'A, A1, B'.\n"
                . "16. Conditions: Look below 'Conditions' or 'Remarks'. If it says 'NONE' or is signature/blank/noise, return empty string.\n";

        if ($fromRawOcrText) {
            $instructions .= "17. Handles merged rows like 'Nationality Sex Date of Birth Weight(kg) Height(m)' followed by 'PHL M 2001/10/05 58 1.64', 'License No. Expiration Date Agency Code' followed by 'G25-24-005686 2029/10/05 G25', 'Blood Type Eyes Color' followed by 'O+ BROWN', and 'DL Codes Conditions' followed by 'A,A1 NONE'. Match each value to its column by position.\n";
        }

        $instructions .= "\nReturn ONLY a pure JSON object with these keys:\n"
                . "- first_name\n"
                . "- last_name\n"
                . "- middle_name\n"
                . "- license_number\n"
                . "- date_of_birth\n"
                . "- gender\n"
                . "- address\n"
                . "- place_of_birth\n"
                . "- license_expiry_date\n"
                . "- license_issued_date\n"
                . "- license_type\n"
                . "- blood_type\n"
                . "- height\n"
                . "- weight\n"
                . "- license_conditions\n"
                . "- license_restriction";

        return $instructions;
    }

    /**
     * Gemini's structured-output schema for the same field contract described
     * in buildExtractionInstructions()'s JSON key list — a secondary
     * robustness layer (every field is guaranteed to be a present string,
     * never a missing key or wrong type) alongside responseMimeType, since a
     * malformed response fails the whole tier the same way "no relevant
     * data" does.
     */
    private function buildResponseSchema(): array
    {
        $properties = [];
        foreach (self::EXTRACTED_FIELDS as $field) {
            $properties[$field] = ['type' => 'string'];
        }

        return [
            'type' => 'object',
            'properties' => $properties,
            'required' => self::EXTRACTED_FIELDS,
        ];
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
                'responseSchema' => $this->buildResponseSchema(),
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

        // Validate BEFORE normalizing gender/license_type — those normalizers
        // would otherwise silently rewrite a leaked label into a fallback
        // value, hiding exactly the failure this guard exists to catch.
        $this->assertNoLabelArtifacts($data);

        if (isset($data['gender'])) {
            $data['gender'] = $this->normalizeGender($data['gender']);
        }
        if (isset($data['license_type'])) {
            $data['license_type'] = $this->normalizeLicenseType($data['license_type']);
        }
        if (isset($data['blood_type'])) {
            $data['blood_type'] = $this->normalizeBloodType($data['blood_type']);
        }
        if (isset($data['height'])) {
            $data['height'] = $this->normalizeHeightToCm($data['height']);
        }
        if (isset($data['license_restriction'])) {
            $data['license_restriction'] = $this->normalizeRestrictionCodes($data['license_restriction']);
        }

        return $data;
    }

    /**
     * Map numeric legacy restriction codes (1, 2, 3, etc.) to standard DL Codes
     * (A, A1, B, B1, B2, C, D, BE, CE) so checkboxes auto-select reliably.
     */
    private function normalizeRestrictionCodes($value)
    {
        $value = strtoupper(trim((string) $value));
        if ($value === '') return '';

        $numericMap = [
            '1' => ['A', 'A1'],
            '2' => ['B', 'B1', 'B2'],
            '3' => ['C'],
            '4' => ['D'],
            '5' => ['BE'],
            '6' => ['A', 'A1'],
            '7' => ['CE'],
            '8' => ['CE'],
        ];

        $tokens = preg_split('/[\s,\-\.]+/', $value);
        $result = [];

        foreach ($tokens as $token) {
            $token = trim($token);
            if (isset($numericMap[$token])) {
                foreach ($numericMap[$token] as $dl) {
                    if (!in_array($dl, $result, true)) {
                        $result[] = $dl;
                    }
                }
            } else if (in_array($token, ['A','A1','B','B1','B2','C','D','BE','CE'], true)) {
                if (!in_array($token, $result, true)) {
                    $result[] = $token;
                }
            }
        }

        return !empty($result) ? implode(', ', $result) : $value;
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
     * Map free-form license-type text to the form's exact <option> values.
     * Unlike gender, there's no sensible "Other" fallback here — the form
     * only offers these two options, so anything that doesn't clearly match
     * one of them is left blank rather than guessed.
     */
    private function normalizeLicenseType($value)
    {
        $value = trim((string) $value);
        if (preg_match('/^non[\s\-]?professional$/i', $value)) {
            return 'Non-Professional';
        }
        if (preg_match('/^professional$/i', $value)) {
            return 'Professional';
        }
        return '';
    }

    /**
     * Only the 8 standard blood types are valid <select> options — anything
     * else (including an OCR misread) is rejected rather than saved, since a
     * wrong blood type is a patient-safety-adjacent risk, not a cosmetic one.
     */
    private function normalizeBloodType($value)
    {
        $value = strtoupper(trim((string) $value));
        $value = str_replace(['0', ' '], ['O', ''], $value);
        return in_array($value, self::VALID_BLOOD_TYPES, true) ? $value : '';
    }

    /**
     * PH licenses print height in meters (e.g. "1.68"); the form field wants
     * whole centimeters. A value in meters (0.50m to 2.50m) is converted to cm;
     * values already in cm (50cm to 250cm) are passed through; stray noise (like 5) is rejected.
     */
    private function normalizeHeightToCm($value)
    {
        $value = trim((string) $value);
        if ($value === '' || !is_numeric($value)) {
            return '';
        }
        $num = (float) $value;
        if ($num >= 0.5 && $num <= 2.5) {
            $num *= 100;
        }
        $cm = (int) round($num);
        return ($cm >= 50 && $cm <= 250) ? (string) $cm : '';
    }

    /**
     * Guards against extractors that latch onto the printed field label
     * itself instead of its value (e.g. an OCR/positional mismatch handing
     * back "Sex" or "Address" verbatim) — this is silently wrong data, not
     * "found nothing", so it must fail the tier rather than be saved.
     *
     * Checks both an exact match (the whole value IS a label, e.g. "Sex")
     * and a "contains" match (a label leaked INTO an otherwise-real-looking
     * value, e.g. ". First Name. Middle Name" from a merged caption line) —
     * the same failure mode public/tvirs-ocr.js guards against client-side,
     * which this previously did not.
     */
    private function assertNoLabelArtifacts(array $data): void
    {
        foreach ([
            'first_name', 'last_name', 'middle_name', 'gender', 'date_of_birth',
            'address', 'license_type', 'blood_type', 'license_conditions',
        ] as $field) {
            $rawValue = (string) ($data[$field] ?? '');
            $value = strtolower(trim($rawValue));
            if ($value === '') {
                continue;
            }
            if (in_array($value, self::LABEL_ARTIFACTS, true)) {
                throw new \Exception("Extraction returned a label artifact for '{$field}': " . $rawValue);
            }
            if ($this->looksContaminatedByLabel($rawValue)) {
                throw new \Exception("Extraction returned a label-contaminated value for '{$field}': " . $rawValue);
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
            '/\b(last\s*name|first\s*name|middle\s*name|given\s*name|apelyido|pangalan|surname|sex|gender'
                . '|date\s*of\s*birth|birth\s*date|dob|address|license\s*no|lic\s*no|id\s*no|control\s*no|card\s*no'
                . '|nationality|expir\w*|valid\s*until|weight|height|blood\s*type|license\s*type|conditions'
                . '|remarks|restrictions|agency\s*code)\b/i',
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
            'place_of_birth' => '',
            'license_expiry_date' => '',
            'license_type' => '',
            'blood_type' => '',
            'height' => '',
            'weight' => '',
            'license_conditions' => '',
            'license_restriction' => '',
        ];

        $fullText = implode(' ', $lines);

        // Find License Number (Format: G25-24-005686, N01-23-456789 or similar)
        // Also fixes common OCR typos where digit '0' is misread as 'O' or 'Q'
        if (preg_match('/[A-Z0-9]{3}[-\s]?[0-9OQ]{2}[-\s]?[0-9OQ]{6}/i', $fullText, $matches)) {
            $rawLic = strtoupper(str_replace(' ', '-', $matches[0]));
            // Replace O/Q with 0 in the numeric parts (after the first letter/code)
            $parts = explode('-', $rawLic);
            if (count($parts) === 3) {
                $parts[1] = str_replace(['O', 'Q'], '0', $parts[1]);
                $parts[2] = str_replace(['O', 'Q'], '0', $parts[2]);
                $data['license_number'] = implode('-', $parts);
            } else {
                $data['license_number'] = str_replace(['O', 'Q'], '0', $rawLic);
            }
        }

        // License Type
        if (preg_match('/professional\s*driver.?s\s*licen[sc]e/i', $fullText)) {
            $data['license_type'] = 'Professional';
        } elseif (preg_match('/driver.?s\s*licen[sc]e/i', $fullText)) {
            $data['license_type'] = 'Non-Professional';
        }

        // Direct pattern for standard PH License row: "PHL M 2001/10/05 58 1.64" or "M 2001/10/05 58 1.64"
        if (preg_match('/\b(?:PHL\s+)?(M|F|Male|Female)\s+(\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2})\s+(\d{2,3})\s+(\d+(?:\.\d+)?)\b/i', $fullText, $phRow)) {
            if ($data['gender'] === '') $data['gender'] = preg_match('/^m/i', $phRow[1]) ? 'Male' : 'Female';
            if ($data['date_of_birth'] === '') {
                $dm = explode('/', str_replace('-', '/', $phRow[2]));
                if (count($dm) === 3) {
                    $data['date_of_birth'] = sprintf('%04d-%02d-%02d', $dm[0], $dm[1], $dm[2]);
                }
            }
            if ($data['weight'] === '') $data['weight'] = (string) (int) round((float) $phRow[3]);
            if ($data['height'] === '') $data['height'] = $this->normalizeHeightToCm($phRow[4]);
        }

        foreach ($lines as $i => $line) {
            // Name row: "Last Name, First Name, Middle Name" -> "CALIDA, KRIS IAN SAPOTALO"
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

            // Expiration Date (standalone or in merged row)
            if (
                $data['license_expiry_date'] === ''
                && preg_match('/(?:Expiration\s*Date|Valid\s*Until|Exp\s*Date)\s*[:\.]?\s*(.*)$/i', $line, $m)
            ) {
                $expRaw = trim($m[1]) !== '' ? trim($m[1]) : ($lines[$i + 1] ?? '');
                if (preg_match('#(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})#', $expRaw, $dm)) {
                    $data['license_expiry_date'] = sprintf('%04d-%02d-%02d', $dm[1], $dm[2], $dm[3]);
                }
            }

            // Standalone "Address" label
            if (
                $data['address'] === ''
                && preg_match('/^(Address|Aduress)\s*[:\-]?\s*(.*)$/i', $line, $m)
            ) {
                $addr = trim($m[2]) !== '' ? trim($m[2]) : ($lines[$i + 1] ?? '');
                if ($addr !== '' && !$this->looksContaminatedByLabel($addr)) {
                    $data['address'] = $addr;
                }
            }

            // Standalone "Blood Type" label
            if (
                $data['blood_type'] === ''
                && preg_match('/Blood\s*Type\s*[:\-]?\s*(\S+)/i', $line, $m)
            ) {
                $bt = strtoupper(trim($m[1]));
                if (in_array($bt, self::VALID_BLOOD_TYPES, true)) {
                    $data['blood_type'] = $bt;
                }
            }

            // DL Codes / Restrictions (e.g. "DL Codes A,A1" or "Restrictions A, A1")
            if (
                $data['license_restriction'] === ''
                && preg_match('/(?:DL\s*Codes?|Restrictions?)\s*[:\-]?\s*(.*)$/i', $line, $m)
            ) {
                $codesRaw = trim($m[1]) !== '' ? trim($m[1]) : ($lines[$i + 1] ?? '');
                if (preg_match_all('/\b(A1?|B[12]?|C|D|BE|CE)\b/i', $codesRaw, $rcMatches)) {
                    $uniqueRc = array_unique(array_map('strtoupper', $rcMatches[0]));
                    $data['license_restriction'] = implode(', ', $uniqueRc);
                }
            }

            // Conditions / Remarks (Filter out "NONE", short noise, and signature text)
            if (
                $data['license_conditions'] === ''
                && preg_match('/^(Conditions|Remarks)\s*[:\-]?\s*(.*)$/i', $line, $m)
            ) {
                $cond = trim($m[2]) !== '' ? trim($m[2]) : trim($lines[$i + 1] ?? '');
                $lowerCond = strtolower($cond);
                if (
                    $cond !== ''
                    && $lowerCond !== 'none'
                    && strlen($cond) >= 5
                    && !preg_match('/(attorney|atty|assistant|secretary|vigor|mendoza|signature|licensee)/i', $cond)
                ) {
                    $data['license_conditions'] = $cond;
                }
            }

            // Merged multi-column header row: "Nationality Sex Date of Birth Weight(kg) Height(m)"
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

                foreach ($columns as $idx => $col) {
                    $val = $values[$idx] ?? '';
                    if ($val === '') continue;

                    if ($data['gender'] === '' && str_contains($col, 'sex') && $this->isPlausibleGenderToken($val)) {
                        $data['gender'] = $val;
                    }
                    if ($data['date_of_birth'] === '' && str_contains($col, 'date of birth')) {
                        if (preg_match('#(\d{4})/(\d{1,2})/(\d{1,2})#', $val, $dm)) {
                            $data['date_of_birth'] = sprintf('%04d-%02d-%02d', $dm[1], $dm[2], $dm[3]);
                        }
                    }
                    if ($data['weight'] === '' && str_contains($col, 'weight') && is_numeric($val)) {
                        $data['weight'] = (string) (int) round((float) $val);
                    }
                    if ($data['height'] === '' && str_contains($col, 'height') && is_numeric($val)) {
                        $data['height'] = $this->normalizeHeightToCm($val);
                    }
                }
            }

            // Merged header row: "License No. Expiration Date Agency Code"
            if (isset($lines[$i + 1]) && preg_match('/License\s*No.*Expiration\s*Date/i', $line)) {
                $valLine = $lines[$i + 1];
                if ($data['license_number'] === '' && preg_match('/[A-Z0-9]{3}[-\s]?[0-9OQ]{2}[-\s]?[0-9OQ]{6}/i', $valLine, $lm)) {
                    $data['license_number'] = str_replace(['O', 'Q'], '0', strtoupper(str_replace(' ', '-', $lm[0])));
                }
                if ($data['license_expiry_date'] === '' && preg_match('#(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})#', $valLine, $em)) {
                    $data['license_expiry_date'] = sprintf('%04d-%02d-%02d', $em[1], $em[2], $em[3]);
                }
            }

            // Merged header row: "DL Codes Conditions"
            if (isset($lines[$i + 1]) && preg_match('/DL\s*Codes.*Conditions/i', $line)) {
                $valLine = $lines[$i + 1];
                if ($data['license_restriction'] === '' && preg_match_all('/\b(A1?|B[12]?|C|D|BE|CE)\b/i', $valLine, $rcMatches)) {
                    $uniqueRc = array_unique(array_map('strtoupper', $rcMatches[0]));
                    $data['license_restriction'] = implode(', ', $uniqueRc);
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
