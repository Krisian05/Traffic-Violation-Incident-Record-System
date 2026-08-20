<?php

namespace App\Http\Controllers;

use App\Models\Lgu;
use Illuminate\Http\Request;

class LguController extends Controller
{
    private function checkGlobalAdmin(): void
    {
        $user = auth()->user();
        if (!$user || (!$user->isSuperAdmin() && !$user->isProvinceAdmin())) {
            abort(403, 'Only Super Administrators or Provincial Administrators can perform this action.');
        }
    }

    private function checkLguAccess(Lgu $lgu): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        if ($user->isSuperAdmin() || $user->isProvinceAdmin()) {
            return;
        }

        if ($user->isLguAdmin() && (int) $user->lgu_id === (int) $lgu->id) {
            return;
        }

        abort(403, 'Forbidden. You may only view or manage your assigned LGU.');
    }

    public function index()
    {
        $user = auth()->user();
        $query = Lgu::withCount(['users', 'violations', 'incidents']);

        if ($user && !$user->isSuperAdmin() && !$user->isProvinceAdmin()) {
            $query->where('id', $user->lgu_id ?? 0);
        }

        $lgus = $query->orderBy('name')->get();
        return view('lgus.index', compact('lgus'));
    }

    public function create()
    {
        $this->checkGlobalAdmin();
        return view('lgus.create');
    }

    public function store(Request $request)
    {
        $this->checkGlobalAdmin();

        $data = $request->validate([
            'code'                    => ['required', 'string', 'max:10', 'alpha_dash', 'unique:lgus,code'],
            'name'                    => ['required', 'string', 'max:150'],
            'province'                => ['required', 'string', 'max:150'],
            'psgc_city_code'          => ['nullable', 'string', 'max:20', 'unique:lgus,psgc_city_code'],
            'ordinance_reference'     => ['nullable', 'string', 'max:255'],
            'treasurer_office'        => ['nullable', 'string', 'max:255'],
            'police_station_name'     => ['nullable', 'string', 'max:255'],
            'police_station_address'  => ['nullable', 'string', 'max:255'],
            'seal'                    => ['nullable', 'image', 'max:10240'],
            'police_chief_name'       => ['nullable', 'string', 'max:255'],
            'police_chief_title'      => ['nullable', 'string', 'max:255'],
            // SMS Gateway
            'sms_provider'            => ['nullable', 'in:textbee,semaphore,local'],
            'sms_api_key'             => ['nullable', 'string', 'max:255'],
            'sms_sender_name'         => ['nullable', 'string', 'max:11'],
            'sms_auto_send'           => ['nullable', 'boolean'],
            'textbee_api_key'         => ['nullable', 'string', 'max:255'],
            'textbee_device_id'       => ['nullable', 'string', 'max:255'],
            // PayMongo Payment Gateway
            'gateway_provider'        => ['nullable', 'in:paymongo,none'],
            'paymongo_public_key'     => ['nullable', 'string', 'max:255'],
            'paymongo_secret_key'     => ['nullable', 'string', 'max:255'],
            'paymongo_webhook_secret' => ['nullable', 'string', 'max:255'],
            'enable_manual_qr_claim'  => ['nullable', 'boolean'],
        ]);

        $data['code']                  = strtoupper($data['code']);
        $data['sms_auto_send']         = $request->boolean('sms_auto_send');
        $data['enable_manual_qr_claim'] = $request->boolean('enable_manual_qr_claim');

        if ($request->hasFile('seal')) {
            $data['seal_path'] = $request->file('seal')->store('seals', uploads_disk());
        }

        $newLgu = Lgu::create($data);

        // Auto-seed default violation types for newly onboarded LGU
        $globalTypes = \App\Models\ViolationType::whereNull('lgu_id')->get();
        foreach ($globalTypes as $type) {
            \App\Models\ViolationType::create([
                'lgu_id'              => $newLgu->id,
                'name'                => $type->name,
                'code'                => $type->code,
                'description'         => $type->description,
                'fine_amount'         => $type->fine_amount,
                'late_penalty_amount' => $type->late_penalty_amount,
                'points'              => $type->points,
            ]);
        }

        return redirect()->route('lgus.index')->with('success', 'LGU added.');
    }

    public function edit(Lgu $lgu)
    {
        $this->checkLguAccess($lgu);
        return view('lgus.edit', compact('lgu'));
    }

    public function update(Request $request, Lgu $lgu)
    {
        $this->checkLguAccess($lgu);

        $data = $request->validate([
            'code'                    => ['required', 'string', 'max:10', 'alpha_dash', "unique:lgus,code,{$lgu->id}"],
            'name'                    => ['required', 'string', 'max:150'],
            'province'                => ['required', 'string', 'max:150'],
            'psgc_city_code'          => ['nullable', 'string', 'max:20', "unique:lgus,psgc_city_code,{$lgu->id}"],
            'ordinance_reference'     => ['nullable', 'string', 'max:255'],
            'treasurer_office'        => ['nullable', 'string', 'max:255'],
            'police_station_name'     => ['nullable', 'string', 'max:255'],
            'police_station_address'  => ['nullable', 'string', 'max:255'],
            'seal'                    => ['nullable', 'image', 'max:10240'],
            'police_chief_name'       => ['nullable', 'string', 'max:255'],
            'police_chief_title'      => ['nullable', 'string', 'max:255'],
            // SMS Gateway
            'sms_provider'            => ['nullable', 'in:textbee,semaphore,local'],
            'sms_api_key'             => ['nullable', 'string', 'max:255'],
            'sms_sender_name'         => ['nullable', 'string', 'max:11'],
            'sms_auto_send'           => ['nullable', 'boolean'],
            'textbee_api_key'         => ['nullable', 'string', 'max:255'],
            'textbee_device_id'       => ['nullable', 'string', 'max:255'],
            // PayMongo Payment Gateway
            'gateway_provider'        => ['nullable', 'in:paymongo,none'],
            'paymongo_public_key'     => ['nullable', 'string', 'max:255'],
            'paymongo_secret_key'     => ['nullable', 'string', 'max:255'],
            'paymongo_webhook_secret' => ['nullable', 'string', 'max:255'],
            'enable_manual_qr_claim'  => ['nullable', 'boolean'],
        ]);

        $data['code']                   = strtoupper($data['code']);
        $data['sms_auto_send']          = $request->boolean('sms_auto_send');
        $data['enable_manual_qr_claim'] = $request->boolean('enable_manual_qr_claim');

        // Blank-means-keep: do not overwrite an existing encrypted key with an empty string.
        // The admin must type a new value to replace it; leaving the field blank preserves
        // whatever is already stored in the database.
        $secretFields = ['sms_api_key', 'textbee_api_key', 'paymongo_secret_key', 'paymongo_webhook_secret'];
        foreach ($secretFields as $field) {
            if (empty($data[$field])) {
                unset($data[$field]);
            }
        }

        if ($request->hasFile('seal')) {
            $data['seal_path'] = $request->file('seal')->store('seals', uploads_disk());
        }

        $lgu->update($data);

        return redirect()->route('lgus.index')->with('success', 'LGU updated.');
    }

    public function destroy(Lgu $lgu)
    {
        $this->checkGlobalAdmin();

        if ($lgu->users()->exists() || $lgu->violations()->exists() || $lgu->incidents()->exists()) {
            return back()->with('error', 'Cannot delete an LGU that has linked users or records.');
        }

        $lgu->delete();

        return redirect()->route('lgus.index')->with('success', 'LGU deleted.');
    }
}
