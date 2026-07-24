<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember("system_setting_{$key}", 3600, function () use ($key) {
            return static::find($key);
        });

        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int' => (int) $setting->value,
            'json', 'array' => json_decode($setting->value, true) ?? $default,
            default => $setting->value,
        };
    }

    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'string', ?string $description = null): self
    {
        $rawValue = match ($type) {
            'boolean' => $value ? '1' : '0',
            'json', 'array' => is_string($value) ? $value : json_encode($value),
            default => (string) $value,
        };

        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value'       => $rawValue,
                'group'       => $group,
                'type'        => $type,
                'description' => $description ?? $key,
            ]
        );

        Cache::forget("system_setting_{$key}");

        return $setting;
    }

    public static function getAllGrouped(): array
    {
        return static::all()->groupBy('group')->toArray();
    }

    public static function seedDefaults(): void
    {
        $defaults = [
            // General System Branding
            'system_name'            => ['value' => 'Traffic Violation Incident Record System', 'group' => 'general', 'type' => 'string', 'desc' => 'Official System Application Title'],
            'system_short_name'      => ['value' => 'TVIRS', 'group' => 'general', 'type' => 'string', 'desc' => 'Short System Acronym'],
            'support_email'          => ['value' => 'support@tvirs.gov.ph', 'group' => 'general', 'type' => 'string', 'desc' => 'System Support Contact Email'],
            'support_phone'          => ['value' => '(02) 8911-1400', 'group' => 'general', 'type' => 'string', 'desc' => 'System Support Contact Phone'],
            
            // Violations & Fine Policies
            'default_grace_period_days' => ['value' => '15', 'group' => 'fine_policy', 'type' => 'integer', 'desc' => 'Default Payment Grace Period in Days'],
            'late_penalty_rate'      => ['value' => '10.00', 'group' => 'fine_policy', 'type' => 'string', 'desc' => 'Percentage or Surcharge for Overdue Payments (%)'],
            'auto_due_date_enabled'  => ['value' => '1', 'group' => 'fine_policy', 'type' => 'boolean', 'desc' => 'Automatically Calculate Due Date on Citation Issue'],

            // Smart Scan / OCR API Settings
            'ocr_enabled'            => ['value' => '1', 'group' => 'ocr', 'type' => 'boolean', 'desc' => 'Enable OCR Driver License & Document Smart Scanning'],
            'ocr_primary_engine'     => ['value' => 'gemini', 'group' => 'ocr', 'type' => 'string', 'desc' => 'Primary OCR Engine (gemini / ocr_space)'],
            'ocr_confidence_min'     => ['value' => '75', 'group' => 'ocr', 'type' => 'integer', 'desc' => 'Minimum Confidence Threshold Percentage (%)'],

            // Financial & Payments
            'online_payments_enabled' => ['value' => '1', 'group' => 'payments', 'type' => 'boolean', 'desc' => 'Enable GCash & Maya Digital Payment Options'],
            'receipt_prefix'         => ['value' => 'OR-', 'group' => 'payments', 'type' => 'string', 'desc' => 'Official Receipt Numbering Prefix'],

            // Security Policies
            'enforce_2fa_admin'      => ['value' => '0', 'group' => 'security', 'type' => 'boolean', 'desc' => 'Mandatory 2FA Enforcement for Administrators'],
            'session_timeout_minutes'=> ['value' => '120', 'group' => 'security', 'type' => 'integer', 'desc' => 'Inactive Session Expiry Timeout (Minutes)'],
            'max_login_attempts'     => ['value' => '5', 'group' => 'security', 'type' => 'integer', 'desc' => 'Maximum Failed Login Attempts Before Lockout'],
            'lockout_duration_minutes' => ['value' => '15', 'group' => 'security', 'type' => 'integer', 'desc' => 'Account Lockout Duration (Minutes)'],

            // System Maintenance & Technical Admin
            'maintenance_mode'       => ['value' => '0', 'group' => 'maintenance', 'type' => 'boolean', 'desc' => 'System Under Scheduled Maintenance'],
            'maintenance_message'    => ['value' => 'System is undergoing scheduled maintenance. Please try again shortly.', 'group' => 'maintenance', 'type' => 'string', 'desc' => 'Public Maintenance Notice Banner'],
            'backup_retention_days'  => ['value' => '30', 'group' => 'maintenance', 'type' => 'integer', 'desc' => 'Database Snapshot Backup Retention (Days)'],
        ];

        foreach ($defaults as $key => $item) {
            if (!static::where('key', $key)->exists()) {
                static::create([
                    'key'         => $key,
                    'value'       => $item['value'],
                    'group'       => $item['group'],
                    'type'        => $item['type'],
                    'description' => $item['desc'],
                ]);
            }
        }
    }
}
