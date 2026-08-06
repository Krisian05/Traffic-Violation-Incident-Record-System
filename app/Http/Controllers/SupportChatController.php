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
                if (str_contains($lowerMsg, mb_strtolower($kw))) {
                    return response()->json([
                        'success'         => true,
                        'answer'          => $faq['answer'],
                        'source'          => 'quick_faq',
                        'daily_remaining' => self::DAILY_LIMIT - (int) Cache::get($this->getDailyCacheKey(), 0),
                    ]);
                }
            }
        }

        // ── 2b. Dynamic Live Database Count Intent Matching (0 API Cost & Instant) ──
        $lguId = $user?->lgu_id;
        $isSuper = $user?->isSuperAdmin() ?? false;
        $lguName = $user?->lgu?->name ?? 'your LGU';

        if (str_contains($lowerMsg, 'how many') || str_contains($lowerMsg, 'total') || str_contains($lowerMsg, 'count')) {
            if (str_contains($lowerMsg, 'motorist') || str_contains($lowerMsg, 'violator') || str_contains($lowerMsg, 'driver')) {
                $count = \App\Models\Violator::when($lguId && !$isSuper, fn($q) => $q->where('lgu_id', $lguId))->count();
                return response()->json([
                    'success' => true,
                    'answer' => "🚗 **Live Registered Motorists & Violators Count**\n\nThere are currently **" . number_format($count) . " registered motorists/violators** saved in the TVIRS database for **{$lguName}**.\n\nYou can search, view, or manage motorist records anytime by navigating to **Motorists** (`/violators`) on the left sidebar.",
                    'source' => 'live_db_count',
                    'daily_remaining' => self::DAILY_LIMIT - (int) Cache::get($this->getDailyCacheKey(), 0),
                ]);
            }

            if (str_contains($lowerMsg, 'vehicle') || str_contains($lowerMsg, 'car') || str_contains($lowerMsg, 'plate')) {
                $count = \App\Models\Vehicle::when($lguId && !$isSuper, fn($q) => $q->where('lgu_id', $lguId))->count();
                return response()->json([
                    'success' => true,
                    'answer' => "🚘 **Live Registered Vehicles Count**\n\nThere are currently **" . number_format($count) . " vehicles** registered in the TVIRS database for **{$lguName}**.\n\nYou can search plate numbers or view impound status under **Vehicles** (`/vehicles`).",
                    'source' => 'live_db_count',
                    'daily_remaining' => self::DAILY_LIMIT - (int) Cache::get($this->getDailyCacheKey(), 0),
                ]);
            }

            if (str_contains($lowerMsg, 'incident') || str_contains($lowerMsg, 'accident')) {
                $count = \App\Models\Incident::when($lguId && !$isSuper, fn($q) => $q->where('lgu_id', $lguId))->count();
                return response()->json([
                    'success' => true,
                    'answer' => "🚩 **Live Road Incidents Count**\n\nThere are currently **" . number_format($count) . " road incidents & traffic events** recorded for **{$lguName}**.\n\nView the full incident registry or report new accidents under **Incidents** (`/incidents`).",
                    'source' => 'live_db_count',
                    'daily_remaining' => self::DAILY_LIMIT - (int) Cache::get($this->getDailyCacheKey(), 0),
                ]);
            }

            if (str_contains($lowerMsg, 'ticket') || str_contains($lowerMsg, 'violation') || str_contains($lowerMsg, 'citation')) {
                $total = \App\Models\Violation::when($lguId && !$isSuper, fn($q) => $q->where('lgu_id', $lguId))->count();
                $unsettled = \App\Models\Violation::when($lguId && !$isSuper, fn($q) => $q->where('lgu_id', $lguId))->where('status', 'unsettled')->count();
                $settled = \App\Models\Violation::when($lguId && !$isSuper, fn($q) => $q->where('lgu_id', $lguId))->where('status', 'settled')->count();
                
                return response()->json([
                    'success' => true,
                    'answer' => "🎫 **Live Citation Tickets Summary**\n\nFor **{$lguName}**, there are:\n- **Total Issued Violations:** " . number_format($total) . "\n- **Unsettled Citations:** " . number_format($unsettled) . "\n- **Settled Citations:** " . number_format($settled) . "\n\nManage tickets under **Violations** (`/violations`) or process settlements at the **Cashier** (`/cashier`).",
                    'source' => 'live_db_count',
                    'daily_remaining' => self::DAILY_LIMIT - (int) Cache::get($this->getDailyCacheKey(), 0),
                ]);
            }

            if (str_contains($lowerMsg, 'collection') || str_contains($lowerMsg, 'payment') || str_contains($lowerMsg, 'money')) {
                $totalColl = \App\Models\Payment::whereNull('voided_at')->when($lguId && !$isSuper, fn($q) => $q->whereHas('violation', fn($v) => $v->where('lgu_id', $lguId)))->sum('amount_paid');
                return response()->json([
                    'success' => true,
                    'answer' => "💵 **Live Treasury Collection Summary**\n\nTotal collections recorded for **{$lguName}** is **₱" . number_format($totalColl, 2) . "**.\n\nView detailed cashier breakdowns and export Excel reconciliation reports under **Collection Reports** (`/payments/report`).",
                    'source' => 'live_db_count',
                    'daily_remaining' => self::DAILY_LIMIT - (int) Cache::get($this->getDailyCacheKey(), 0),
                ]);
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
                'total_collections'    => \App\Models\Payment::whereNull('voided_at')->when($lguId && !$isSuper, fn($q) => $q->whereHas('violation', fn($v) => $v->where('lgu_id', $lguId)))->sum('amount_paid'),
            ];

            $systemPrompt = TvrsKnowledgeBase::getSystemPrompt(
                $user?->name ?? 'User',
                $user?->role_label ?? ($user?->role ?? 'Staff'),
                $user?->lgu?->name ?? 'LGU',
                $stats
            );

            $payloadPrimary = [
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $systemPrompt]
                    ]
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $message]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature'     => 0.3,
                    'maxOutputTokens' => 1200,
                ]
            ];

            $model = 'gemini-flash-latest';
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $response = Http::timeout(15)->withoutVerifying()->post($endpoint, $payloadPrimary);

            // Fallback to gemini-3.6-flash if gemini-flash-latest fails
            if (!$response->successful()) {
                $endpointFallback = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key={$apiKey}";
                $payloadFallback = $payloadPrimary;
                $payloadFallback['generationConfig']['maxOutputTokens'] = 2500;

                $response = Http::timeout(20)->withoutVerifying()->post($endpointFallback, $payloadFallback);
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
