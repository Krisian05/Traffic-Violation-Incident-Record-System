<?php

namespace App\Console\Commands;

use App\Models\Violation;
use App\Services\SmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Send72HourSmsReminders extends Command
{
    protected $signature = 'sms:send-72h-reminders';
    protected $description = 'Automatically send 72-hour SMS payment reminders to motorists with pending violations';

    public function handle(SmsService $smsService): int
    {
        $cutoffTime = now()->subHours(72);

        // Find violations created at least 72 hours ago that are pending/partial and haven't received a reminder
        $violations = Violation::with(['violator', 'violationType', 'lgu'])
            ->whereIn('status', ['pending', 'partial'])
            ->where('created_at', '<=', $cutoffTime)
            ->whereNull('sms_reminder_sent_at')
            ->whereHas('violator', fn($q) => $q->whereNotNull('contact_number')->where('contact_number', '!=', ''))
            ->get();

        $count = 0;
        foreach ($violations as $violation) {
            // Respect LGU auto-send setting if LGU is attached
            if ($violation->lgu && !$violation->lgu->sms_auto_send) {
                continue;
            }

            $result = $smsService->send72HourReminderSms($violation);
            if ($result['success']) {
                $count++;
            }
        }

        $this->info("72-Hour SMS Reminder: Successfully dispatched {$count} reminder(s).");
        Log::info("72-Hour SMS Reminder Command: Dispatched {$count} reminder(s).");

        return Command::SUCCESS;
    }
}
