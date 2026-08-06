<?php

namespace App\Http\Controllers;

use App\Services\TvrsKnowledgeBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupportChatController extends Controller
{
    private const DAILY_LIMIT = 1500;

    /**
     * Get Quick FAQs list for rendering initial chat pills.
     */
    public function getFaqs()
    {
        return response()->json([
            'success' => true,
            'faqs' => TvrsKnowledgeBase::getQuickFaqs()
        ]);
    }

    /**
     * Process user chat query.
     */
    public function query(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'faq_id'  => 'nullable|string',
        ]);

        $message = trim($request->input('message'));
        $faqId   = $request->input('faq_id');
        $user    = Auth::user();

        // ── 1. Check if user selected a Quick FAQ pill (0 API Cost) ──
        if ($faqId) {
            foreach (TvrsKnowledgeBase::getQuickFaqs() as $faq) {
                if ($faq['id'] === $faqId) {
                    return response()->json([
                        'success'         => true,
                        'answer'          => $faq['answer'],
                        'source'          => 'quick_faq',
                        'daily_remaining' => self::DAILY_LIMIT - (int) Cache::get($this->getDailyCacheKey(), 0),
                    ]);
                }
            }
        }

        // ── 2. Check Keyword Match in Quick FAQs (0 API Cost) ──
        $lowerMsg = mb_strtolower($message);
        foreach (TvrsKnowledgeBase::getQuickFaqs() as $faq) {
            foreach ($faq['keywords'] as $kw) {
                if (str_contains($lowerMsg, mb_strtolower($kw)) && mb_strlen($message) < 40) {
                    return response()->json([
                        'success'         => true,
                        'answer'          => $faq['answer'],
                        'source'          => 'quick_faq',
                        'daily_remaining' => self::DAILY_LIMIT - (int) Cache::get($this->getDailyCacheKey(), 0),
                    ]);
                }
            }
        }

        // ── 3. Check Daily 1,500 Limit Guard ──
        $cacheKey  = $this->getDailyCacheKey();
        $dailyUsed = (int) Cache::get($cacheKey, 0);

        if ($dailyUsed >= self::DAILY_LIMIT) {
            return response()->json([
                'success' => true,
                'source'  => 'daily_limit_reached',
                'answer'  => "🤖 **AI Chat Support Daily Limit Reached (1,500/1,500)**\n\nThe automated AI assistant has reached its 1,500 daily query limit for today to prevent unexpected API costs. The limit will automatically reset tomorrow at 12:00 AM.\n\n*In the meantime, you can still click any of the Quick FAQs above for instant zero-cost help!*",
                'daily_used'      => self::DAILY_LIMIT,
                'daily_limit'     => self::DAILY_LIMIT,
                'daily_remaining' => 0,
            ]);
        }

        // ── 4. Call Gemini AI API ──
        $apiKey = config('services.gemini.key');

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'GEMINI_API_KEY is not configured in .env file.'
            ], 500);
        }

        try {
            $lguId = $user?->lgu_id;
            $isSuper = $user?->isSuperAdmin() ?? false;

            $stats = [
                'total_motorists'      => \App\Models\Violator::when($lguId && !$isSuper, fn($q) => $q->where('lgu_id', $lguId))->count(),
                'total_vehicles'       => \App\Models\Vehicle::when($lguId && !$isSuper, fn($q) => $q->where('lgu_id', $lguId))->count(),
                'total_violations'     => \App\Models\Violation::when($lguId && !$isSuper, fn($q) => $q->where('lgu_id', $lguId))->count(),
                'unsettled_violations' => \App\Models\Violation::when($lguId && !$isSuper, fn($q) => $q->where('lgu_id', $lguId))->where('status', 'unsettled')->count(),
                'settled_violations'   => \App\Models\Violation::when($lguId && !$isSuper, fn($q) => $q->where('lgu_id', $lguId))->where('status', 'settled')->count(),
                'total_incidents'      => \App\Models\Incident::when($lguId && !$isSuper, fn($q) => $q->where('lgu_id', $lguId))->count(),
                'total_collections'    => \App\Models\Payment::when($lguId && !$isSuper, fn($q) => $q->where('lgu_id', $lguId))->sum('amount'),
            ];

            $systemPrompt = TvrsKnowledgeBase::getSystemPrompt(
                $user?->name ?? 'User',
                $user?->role_label ?? ($user?->role ?? 'Staff'),
                $user?->lgu?->name ?? 'LGU',
                $stats
            );

            $model = 'gemini-flash-latest';
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $response = Http::timeout(15)->withoutVerifying()->post($endpoint, [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $systemPrompt . "\n\nUser Question: " . $message]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature'     => 0.3,
                    'maxOutputTokens' => 1200,
                ]
            ]);

            // Fallback to gemini-3.6-flash if gemini-flash-latest fails
            if (!$response->successful()) {
                $endpointFallback = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key={$apiKey}";
                $response = Http::timeout(20)->withoutVerifying()->post($endpointFallback, [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $systemPrompt . "\n\nUser Question: " . $message]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature'     => 0.3,
                        'maxOutputTokens' => 2500,
                    ]
                ]);
            }

            if ($response->successful()) {
                $json = $response->json();
                $answerText = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if ($answerText) {
                    // Increment Daily Usage counter
                    $newDailyCount = Cache::increment($cacheKey);
                    if ($newDailyCount === 1) {
                        Cache::put($cacheKey, 1, now()->endOfDay());
                    }

                    return response()->json([
                        'success'         => true,
                        'answer'          => trim($answerText),
                        'source'          => 'ai',
                        'daily_used'      => $newDailyCount,
                        'daily_limit'     => self::DAILY_LIMIT,
                        'daily_remaining' => max(0, self::DAILY_LIMIT - $newDailyCount),
                    ]);
                }
            }

            Log::error('Gemini API Error: ' . $response->body());
            return response()->json([
                'success' => false,
                'message' => 'Unable to get response from AI provider. Please try again or tap one of the Quick FAQs.'
            ]);

        } catch (\Exception $e) {
            Log::error('Chat Support Exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request: ' . $e->getMessage()
            ]);
        }
    }

    private function getDailyCacheKey(): string
    {
        return 'gemini_daily_requests_' . now()->format('Y-m-d');
    }
}
