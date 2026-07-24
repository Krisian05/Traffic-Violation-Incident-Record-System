<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class SystemConfigController extends Controller
{
    public function index(): View
    {
        SystemSetting::seedDefaults();
        $settingsGrouped = SystemSetting::getAllGrouped();

        return view('admin.system-config.index', compact('settingsGrouped'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'system_name'              => ['required', 'string', 'max:150'],
            'system_short_name'        => ['required', 'string', 'max:50'],
            'support_email'            => ['required', 'email', 'max:150'],
            'support_phone'            => ['required', 'string', 'max:50'],
            'default_grace_period_days'=> ['required', 'integer', 'min:1', 'max:365'],
            'late_penalty_rate'        => ['required', 'numeric', 'min:0', 'max:100'],
            'auto_due_date_enabled'    => ['nullable', 'boolean'],
            'ocr_enabled'              => ['nullable', 'boolean'],
            'ocr_primary_engine'       => ['required', 'in:gemini,ocr_space'],
            'ocr_confidence_min'       => ['required', 'integer', 'min:1', 'max:100'],
            'online_payments_enabled'  => ['nullable', 'boolean'],
            'receipt_prefix'           => ['required', 'string', 'max:20'],
            'enforce_2fa_admin'        => ['nullable', 'boolean'],
            'session_timeout_minutes'  => ['required', 'integer', 'min:5', 'max:10080'],
            'max_login_attempts'       => ['required', 'integer', 'min:1', 'max:20'],
            'lockout_duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'maintenance_mode'         => ['nullable', 'boolean'],
            'maintenance_message'      => ['required', 'string', 'max:500'],
            'backup_retention_days'    => ['required', 'integer', 'min:1', 'max:3650'],
        ]);

        $booleanFields = [
            'auto_due_date_enabled',
            'ocr_enabled',
            'online_payments_enabled',
            'enforce_2fa_admin',
            'maintenance_mode',
        ];

        foreach ($booleanFields as $field) {
            $data[$field] = isset($data[$field]) && $data[$field] ? '1' : '0';
        }

        $groupMapping = [
            'system_name'              => 'general',
            'system_short_name'        => 'general',
            'support_email'            => 'general',
            'support_phone'            => 'general',
            'default_grace_period_days'=> 'fine_policy',
            'late_penalty_rate'        => 'fine_policy',
            'auto_due_date_enabled'    => 'fine_policy',
            'ocr_enabled'              => 'ocr',
            'ocr_primary_engine'       => 'ocr',
            'ocr_confidence_min'       => 'ocr',
            'online_payments_enabled'  => 'payments',
            'receipt_prefix'           => 'payments',
            'enforce_2fa_admin'        => 'security',
            'session_timeout_minutes'  => 'security',
            'max_login_attempts'       => 'security',
            'lockout_duration_minutes' => 'security',
            'maintenance_mode'         => 'maintenance',
            'maintenance_message'      => 'maintenance',
            'backup_retention_days'    => 'maintenance',
        ];

        $typeMapping = [
            'auto_due_date_enabled'    => 'boolean',
            'ocr_enabled'              => 'boolean',
            'online_payments_enabled'  => 'boolean',
            'enforce_2fa_admin'        => 'boolean',
            'maintenance_mode'         => 'boolean',
            'default_grace_period_days'=> 'integer',
            'ocr_confidence_min'       => 'integer',
            'session_timeout_minutes'  => 'integer',
            'max_login_attempts'       => 'integer',
            'lockout_duration_minutes' => 'integer',
            'backup_retention_days'    => 'integer',
        ];

        foreach ($data as $key => $val) {
            $group = $groupMapping[$key] ?? 'general';
            $type = $typeMapping[$key] ?? 'string';
            SystemSetting::set($key, $val, $group, $type);
        }

        activity()
            ->causedBy(auth()->user())
            ->useLog('system')
            ->log('Updated system configuration settings.');

        return redirect()->route('admin.system-config.index')
            ->with('success', 'Overall System Configuration updated successfully.');
    }
}
