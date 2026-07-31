<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceRegistration extends Model
{
    protected $fillable = [
        'user_id',
        'token_hash',
        'label',
        'user_agent',
        'ip_address',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Formatted label including Phone Brand/Model, OS, and Browser.
     */
    public function getFormattedLabelAttribute(): string
    {
        $ua = $this->user_agent ?? '';
        $label = $this->label ?? '';

        $phoneModel = static::parsePhoneModel($ua, $label);
        $browser = static::parseBrowser($ua);

        if ($phoneModel && $browser) {
            return "{$phoneModel} · {$browser}";
        }

        return $phoneModel ?: ($label ?: 'Unknown Device');
    }

    /**
     * Determine icon class for device type (mobile phone, tablet, or desktop).
     */
    public function getDeviceIconAttribute(): string
    {
        $ua = strtolower($this->user_agent ?? '');

        if (str_contains($ua, 'ipad') || str_contains($ua, 'tablet')) {
            return 'bi-tablet-fill';
        }

        if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
            return 'bi-phone-fill';
        }

        return 'bi-display-fill';
    }

    /**
     * Parse phone brand and model from User-Agent or custom label.
     */
    public static function parsePhoneModel(?string $userAgent, ?string $fallbackLabel = null): string
    {
        if (empty($userAgent)) {
            return $fallbackLabel ?: 'Unknown Device';
        }

        // 1. Check for Android phone models: "Android <version>; <Model>"
        if (preg_match('/Android\s+([0-9\.]+);\s*([^;\)\/]+)/i', $userAgent, $matches)) {
            $rawModel = trim($matches[2]);
            $androidVer = 'Android ' . $matches[1];

            // Remove Build/xxxx if appended
            $rawModel = preg_replace('/ Build\/.*/i', '', $rawModel);

            $brand = static::resolveBrandName($rawModel);
            return "{$brand} ({$androidVer})";
        }

        // 2. Check for iPhone / iPad
        if (preg_match('/iPhone\s+OS\s+([0-9_]+)/i', $userAgent, $matches)) {
            $version = str_replace('_', '.', $matches[1]);
            return "Apple iPhone (iOS {$version})";
        }

        if (preg_match('/iPad.*OS\s+([0-9_]+)/i', $userAgent, $matches)) {
            $version = str_replace('_', '.', $matches[1]);
            return "Apple iPad (iPadOS {$version})";
        }

        // 3. Desktop Operating Systems
        if (str_contains($userAgent, 'Windows NT 10.0') || str_contains($userAgent, 'Windows NT 11.0')) {
            return 'Windows 10/11 PC';
        }
        if (str_contains($userAgent, 'Windows NT 6.3')) {
            return 'Windows 8.1 PC';
        }
        if (str_contains($userAgent, 'Windows NT 6.1')) {
            return 'Windows 7 PC';
        }
        if (str_contains($userAgent, 'Windows')) {
            return 'Windows PC';
        }
        if (str_contains($userAgent, 'Macintosh') || str_contains($userAgent, 'Mac OS X')) {
            return 'Apple Mac';
        }
        if (str_contains($userAgent, 'Linux') && !str_contains($userAgent, 'Android')) {
            return 'Linux PC';
        }

        return $fallbackLabel ?: 'Unknown Device';
    }

    /**
     * Resolves human-readable brand name for raw model codes.
     */
    protected static function resolveBrandName(string $model): string
    {
        $m = strtoupper($model);

        // Samsung
        if (str_starts_with($m, 'SM-') || str_starts_with($m, 'GT-') || str_starts_with($m, 'SCH-') || str_contains($m, 'SAMSUNG')) {
            return "Samsung " . $model;
        }

        // Xiaomi / Redmi / POCO
        if (str_contains($m, 'REDMI')) {
            return "Xiaomi " . $model;
        }
        if (str_contains($m, 'POCO')) {
            return "Xiaomi " . $model;
        }
        if (str_starts_with($m, 'MI ') || str_starts_with($m, '220') || str_starts_with($m, '210') || str_starts_with($m, 'M20') || str_starts_with($m, 'M21')) {
            return "Xiaomi ({$model})";
        }

        // OPPO
        if (str_starts_with($m, 'CPH') || str_contains($m, 'OPPO')) {
            return "OPPO " . $model;
        }

        // Vivo
        if (str_starts_with($m, 'V2') || str_starts_with($m, 'V1') || str_contains($m, 'VIVO')) {
            return "Vivo " . $model;
        }

        // Realme
        if (str_starts_with($m, 'RMX') || str_contains($m, 'REALME')) {
            return "Realme " . $model;
        }

        // Google Pixel
        if (str_contains($m, 'PIXEL')) {
            return "Google " . $model;
        }

        // Infinix
        if (str_contains($m, 'INFINIX') || str_starts_with($m, 'X6')) {
            return "Infinix " . $model;
        }

        // Tecno
        if (str_contains($m, 'TECNO') || str_starts_with($m, 'CK') || str_starts_with($m, 'LG')) {
            return "Tecno " . $model;
        }

        // Huawei
        if (str_contains($m, 'HUAWEI') || preg_match('/^(VOG|ELE|ANA|EML|MAR|POT|JNY|JKM)-/i', $model)) {
            return "Huawei " . $model;
        }

        // OnePlus
        if (str_contains($m, 'ONEPLUS') || preg_match('/^(IN20|NE22|DN21|CPH2411)/i', $model)) {
            return "OnePlus " . $model;
        }

        return "Android Phone ({$model})";
    }

    /**
     * Parse browser name from User-Agent.
     */
    public static function parseBrowser(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'Browser';
        }

        return match (true) {
            str_contains($userAgent, 'SamsungBrowser') => 'Samsung Internet',
            str_contains($userAgent, 'Edg/')            => 'Microsoft Edge',
            str_contains($userAgent, 'OPR/') || str_contains($userAgent, 'Opera') => 'Opera',
            str_contains($userAgent, 'Chrome')         => 'Chrome',
            str_contains($userAgent, 'Firefox')        => 'Firefox',
            str_contains($userAgent, 'Safari')         => 'Safari',
            default                                    => 'Browser',
        };
    }
}
