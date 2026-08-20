<?php

namespace App\Http\Controllers;

use App\Models\Lgu;
use App\Models\Violation;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $lguId = $user->lgu_id;

        // Fetch LGU for settings (if Super Admin without LGU, use first or selected LGU)
        if ($user->isSuperAdmin()) {
            $selectedLguId = $request->input('lgu_id');
            $lgu = $selectedLguId ? Lgu::find($selectedLguId) : ($lguId ? Lgu::find($lguId) : Lgu::first());
            $lgus = Lgu::orderBy('name')->get();
        } else {
            $lgu = $user->lgu ? $user->lgu : Lgu::first();
            $lgus = collect([$lgu]);
        }

        // Query violations with SMS activity
        $query = Violation::with(['violator', 'violationType', 'lgu'])
            ->where(function ($q) {
                $q->whereIn('sms_status', ['sent', 'failed'])
                  ->orWhereNotNull('sms_sent_at');
            });

        if ($lgu) {
            $query->where('lgu_id', $lgu->id);
        }

        $smsLogs = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();

        // Metrics
        $totalSent = Violation::when($lgu, fn($q) => $q->where('lgu_id', $lgu->id))->where('sms_status', 'sent')->count();
        $totalFailed = Violation::when($lgu, fn($q) => $q->where('lgu_id', $lgu->id))->where('sms_status', 'failed')->count();
        $totalPending = Violation::when($lgu, fn($q) => $q->where('lgu_id', $lgu->id))->where('sms_status', 'none')->count();

        return view('sms.index', compact('lgu', 'lgus', 'smsLogs', 'totalSent', 'totalFailed', 'totalPending'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'lgu_id'            => ['required', 'exists:lgus,id'],
            'sms_provider'      => ['required', 'in:textbee,semaphore,local'],
            'textbee_api_key'   => ['nullable', 'string', 'max:255'],
            'textbee_device_id' => ['nullable', 'string', 'max:255'],
            'sms_api_key'       => ['nullable', 'string', 'max:255'],
            'sms_sender_name'   => ['nullable', 'string', 'max:11'],
            'sms_auto_send'     => ['nullable', 'boolean'],
        ]);

        $lgu = Lgu::findOrFail($data['lgu_id']);
        
        // Authorization check: User must be admin of this LGU or Super Admin
        $user = Auth::user();
        if (!$user->isSuperAdmin() && $user->lgu_id !== $lgu->id) {
            abort(403, 'Unauthorized to update SMS settings for this LGU.');
        }

        $updateData = [
            'sms_provider'      => $data['sms_provider'] ?? 'textbee',
            'textbee_device_id' => $data['textbee_device_id'] ?? null,
            'sms_sender_name'   => ($data['sms_sender_name'] ?? null) ?: 'TVIRS',
            'sms_auto_send'     => $request->boolean('sms_auto_send'),
        ];

        // Blank-means-keep: only update secret API keys if a new non-empty value was provided
        if (!empty($data['textbee_api_key'])) {
            $updateData['textbee_api_key'] = $data['textbee_api_key'];
        }
        if (!empty($data['sms_api_key'])) {
            $updateData['sms_api_key'] = $data['sms_api_key'];
        }

        $lgu->update($updateData);

        return back()->with('success', 'SMS Gateway configuration updated successfully.');
    }
}
