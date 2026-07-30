<?php

namespace App\Http\Controllers;

use App\Models\Violator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    /**
     * Live search motorists by name or license number for DSR auto-complete dropdown.
     * Scoped to the authenticated user's LGU if assigned (e.g. cashier, operator, LGU admin).
     */
    public function searchMotorists(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $user = Auth::user();

        $query = Violator::query();

        // Scope by LGU / region if user belongs to a specific LGU
        if ($user && $user->lgu_id) {
            $query->where('lgu_id', $user->lgu_id);
        }

        $searchTerm = "%{$q}%";
        $motorists = $query->where(function ($sq) use ($searchTerm) {
            $sq->where('first_name', 'LIKE', $searchTerm)
               ->orWhere('middle_name', 'LIKE', $searchTerm)
               ->orWhere('last_name', 'LIKE', $searchTerm)
               ->orWhere('license_number', 'LIKE', $searchTerm)
               ->orWhere('email', 'LIKE', $searchTerm);
        })
        ->with(['violations' => function ($vq) {
            $vq->latest()->limit(1);
        }])
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->limit(10)
        ->get();

        $results = $motorists->map(function ($m) {
            $latestViolation = $m->violations->first();
            return [
                'id'             => $m->id,
                'full_name'      => $m->full_name,
                'email'          => $m->email ?? '',
                'contact_number' => $m->contact_number ?? '',
                'license_number' => $m->license_number ?? '',
                'ticket_number'  => $latestViolation ? $latestViolation->ticket_number : '',
            ];
        });

        return response()->json($results);
    }

    public function submitDsr(Request $request)
    {
        $data = $request->validate([
            'full_name'      => ['required', 'string', 'max:150'],
            'email'          => ['nullable', 'email', 'max:150'],
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
                'email'          => $data['email'] ?? null,
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
