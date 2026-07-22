<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OcrController extends Controller
{
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

    private function scanWithGemini($base64Image)
    {
        $apiKey = config('services.gemini.key');
        if (!$apiKey) {
            throw new \Exception('Gemini API key not configured.');
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey;

        $prompt = "Extract the following details from this driver's license or ID card. Return ONLY a pure JSON object, without any markdown formatting or backticks. If a field cannot be found, return an empty string for it. The JSON must exactly match these keys:\n"
                . "- first_name\n"
                . "- last_name\n"
                . "- middle_name\n"
                . "- license_number (or ID number)\n"
                . "- date_of_birth (Format: YYYY-MM-DD)\n"
                . "- gender\n"
                . "- address";

        $response = Http::post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inlineData' => [
                                'mimeType' => 'image/jpeg',
                                'data' => $base64Image
                            ]
                        ]
                    ]
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
            throw new \Exception('Gemini found no relevant data.');
        }

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
            throw new \Exception('OCR.Space error: ' . ($json['ErrorMessage'][0] ?? 'Unknown'));
        }

        $parsedText = $json['ParsedResults'][0]['ParsedText'] ?? '';
        if (empty($parsedText)) {
            throw new \Exception('OCR.Space returned empty text.');
        }

        return $this->parseRawText($parsedText);
    }

    /**
     * Basic parsing fallback for OCR.Space raw text
     */
    private function parseRawText($text)
    {
        $lines = array_map('trim', explode("\n", $text));
        $data = [
            'license_number' => '',
            'first_name' => '',
            'last_name' => '',
            'middle_name' => '',
            'date_of_birth' => '',
            'gender' => '',
            'address' => '',
        ];

        // Very basic heuristic parsing since OCR.Space returns raw text
        $fullText = implode(" ", $lines);
        
        // Find License Number (Format: N01-23-456789 or similar)
        if (preg_match('/[A-Z]\d{2}-\d{2}-\d{6}/', $fullText, $matches)) {
            $data['license_number'] = $matches[0];
        }

        // Fallback parsing logic (usually name is first few lines)
        // This is a naive fallback because building a perfect regex for Philippine DL is extremely hard.
        // It's just a fallback.
        foreach ($lines as $i => $line) {
            if (preg_match('/(Last Name|Apelyido)/i', $line) && isset($lines[$i+1])) {
                $data['last_name'] = $lines[$i+1];
            }
            if (preg_match('/(First Name|Pangalan)/i', $line) && isset($lines[$i+1])) {
                $data['first_name'] = $lines[$i+1];
            }
            if (preg_match('/^(Sex|Gender)\s*[:\-]?\s*(.+)$/i', $line, $genderMatch)) {
                $data['gender'] = $genderMatch[2];
            }
        }

        if ($data['gender'] !== '') {
            $data['gender'] = $this->normalizeGender($data['gender']);
        }

        return $data;
    }
}
