<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PrivacyController extends Controller
{
    public function policy()
    {
        return view('privacy.policy');
    }

    public function showDsrForm()
    {
        return view('privacy.dsr');
    }

    public function submitDsr(Request $request)
    {
        $data = $request->validate([
            'full_name'      => ['required', 'string', 'max:150'],
            'email'          => ['required', 'email', 'max:150'],
            'contact_number' => ['required', 'string', 'max:30'],
            'license_number' => ['nullable', 'string', 'max:50'],
            'ticket_number'  => ['nullable', 'string', 'max:50'],
            'request_type'   => ['required', 'in:access,correction,erasure,objection,inquiry'],
            'details'        => ['required', 'string', 'max:2000'],
        ]);

        activity()
            ->useLog('privacy')
            ->event('data_subject_request')
            ->withProperties([
                'ip'             => $request->ip(),
                'user_agent'     => $request->userAgent(),
                'full_name'      => $data['full_name'],
                'email'          => $data['email'],
                'request_type'   => $data['request_type'],
                'ticket_number'  => $data['ticket_number'] ?? null,
                'license_number' => $data['license_number'] ?? null,
            ])
            ->log("Data Subject Request submitted ({$data['request_type']}) by {$data['full_name']}");

        app(\App\Services\NotificationService::class)->notifyDsrSubmitted($data);

        return redirect()->route('privacy.dsr')
            ->with('success', 'Your Data Subject Request has been received and logged. Our Data Protection Officer (DPO) will review your request in compliance with Republic Act No. 10173 within fifteen (15) working days.');
    }
}
