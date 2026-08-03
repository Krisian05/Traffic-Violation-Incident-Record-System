<?php

namespace App\Services;

use App\Models\Violation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send citation issuance SMS notification to motorist.
     */
    public function sendCitationSms(Violation $violation): array
    {
        $violator = $violation->violator;
        $recipient = $violator?->contact_number;

        if (empty($recipient)) {
            $violation->update([
                'sms_status' => 'failed',
                'sms_error' => 'No contact number recorded for motorist',
            ]);

            return ['success' => false, 'message' => 'No contact number recorded for motorist'];
        }

        $lguName = $violation->lgu?->name ?? 'LGU Traffic Office';
        $violationType = $violation->type?->name ?? 'Traffic Violation';
        $ticketNo = $violation->ticket_number ?? 'CIT-' . $violation->id;
        $fine = number_format($violation->type?->penalty_amount ?? 0, 2);
        $dueDate = $violation->due_date ? $violation->due_date->format('M d, Y') : 'N/A';

        $message = "TVIRS Citation #{$ticketNo}: Issued for {$violationType} at {$lguName}. Fine: P{$fine}, Due Date: {$dueDate}. Please settle at LGU Treasurer or scan ticket QR.";

        return $this->dispatch($violation, $recipient, $message);
    }

    /**
     * Send payment due date reminder SMS.
     */
    public function sendPaymentReminderSms(Violation $violation): array
    {
        $violator = $violation->violator;
        $recipient = $violator?->contact_number;

        if (empty($recipient)) {
            return ['success' => false, 'message' => 'No contact number recorded for motorist'];
        }

        $ticketNo = $violation->ticket_number ?? 'CIT-' . $violation->id;
        $fine = number_format($violation->type?->penalty_amount ?? 0, 2);
        $dueDate = $violation->due_date ? $violation->due_date->format('M d, Y') : 'N/A';

        $message = "TVIRS Reminder: Citation #{$ticketNo} fine of P{$fine} is due on {$dueDate}. Settle early at LGU Treasurer to avoid late penalties.";

        return $this->dispatch($violation, $recipient, $message);
    }

    /**
     * Execute SMS HTTP POST to Semaphore API or fallback log.
     */
    protected function dispatch(Violation $violation, string $recipient, string $message): array
    {
        $lgu = $violation->lgu;
        $provider = $lgu?->sms_provider ?? 'textbee';

        // Format phone number to international +63 format
        $cleanPhone = preg_replace('/[^0-9+]/', '', $recipient);
        if (str_starts_with($cleanPhone, '09')) {
            $cleanPhone = '+639' . substr($cleanPhone, 2);
        } elseif (str_starts_with($cleanPhone, '9') && strlen($cleanPhone) === 10) {
            $cleanPhone = '+63' . $cleanPhone;
        }

        // 1. Android SIM Gateway via Textbee.dev
        if ($provider === 'textbee') {
            $apiKey = $lgu?->textbee_api_key ?: env('TEXTBEE_API_KEY');
            $deviceId = $lgu?->textbee_device_id ?: env('TEXTBEE_DEVICE_ID');

            if (!empty($apiKey) && !empty($deviceId)) {
                try {
                    $response = Http::withHeaders([
                        'x-api-key' => $apiKey,
                    ])->post("https://api.textbee.dev/api/v1/gateway/devices/{$deviceId}/send-sms", [
                        'recipients' => [$cleanPhone],
                        'message'    => $message,
                    ]);

                    if ($response->successful()) {
                        $violation->update([
                            'sms_status'  => 'sent',
                            'sms_sent_at' => now(),
                            'sms_error'   => null,
                        ]);

                        Log::info("SMS Gateway (Textbee Android SIM) sent ticket #{$violation->ticket_number} to {$cleanPhone}");

                        return ['success' => true, 'message' => 'SMS citation sent via Android SIM Gateway (Textbee)'];
                    }

                    $errorMsg = 'Textbee Gateway Error: ' . $response->body();
                    $violation->update([
                        'sms_status' => 'failed',
                        'sms_error'  => $errorMsg,
                    ]);

                    return ['success' => false, 'message' => $errorMsg];
                } catch (\Throwable $e) {
                    $errorMsg = 'Textbee Connection Error: ' . $e->getMessage();
                    $violation->update([
                        'sms_status' => 'failed',
                        'sms_error'  => $errorMsg,
                    ]);

                    return ['success' => false, 'message' => $errorMsg];
                }
            }
        }

        // 2. Semaphore API Gateway
        if ($provider === 'semaphore') {
            $apiKey = $lgu?->sms_api_key ?: config('services.semaphore.api_key', env('SEMAPHORE_API_KEY'));
            $senderName = $lgu?->sms_sender_name ?: config('services.semaphore.sender_name', 'TVIRS');

            if (!empty($apiKey)) {
                try {
                    $response = Http::asForm()->post('https://api.semaphore.co/api/v4/messages', [
                        'apikey'     => $apiKey,
                        'number'     => $cleanPhone,
                        'message'    => $message,
                        'sendername' => $senderName,
                    ]);

                    if ($response->successful()) {
                        $violation->update([
                            'sms_status'  => 'sent',
                            'sms_sent_at' => now(),
                            'sms_error'   => null,
                        ]);

                        Log::info("SMS Gateway (Semaphore) sent ticket #{$violation->ticket_number} to {$cleanPhone}");

                        return ['success' => true, 'message' => 'SMS notification dispatched via Semaphore API'];
                    }

                    $errorMsg = 'Semaphore API Error: ' . $response->body();
                    $violation->update([
                        'sms_status' => 'failed',
                        'sms_error'  => $errorMsg,
                    ]);

                    return ['success' => false, 'message' => $errorMsg];
                } catch (\Throwable $e) {
                    $errorMsg = 'Semaphore Dispatch Error: ' . $e->getMessage();
                    $violation->update([
                        'sms_status' => 'failed',
                        'sms_error'  => $errorMsg,
                    ]);

                    return ['success' => false, 'message' => $errorMsg];
                }
            }
        }

        // 3. Fallback Development / Log Gateway
        $violation->update([
            'sms_status'  => 'sent',
            'sms_sent_at' => now(),
            'sms_error'   => 'Logged locally (No active Gateway API key set)',
        ]);

        Log::info("SMS [LOCAL LOG GATEWAY] To: {$cleanPhone} | Message: {$message}");

        return ['success' => true, 'message' => 'SMS logged locally (Configure Textbee API Key & Device ID in SMS Gateway Settings for free SIM sending)'];
    }
}
