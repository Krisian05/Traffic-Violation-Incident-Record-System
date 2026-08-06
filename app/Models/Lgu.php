<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lgu extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'province',
        'psgc_city_code',
        'ordinance_reference',
        'treasurer_office',
        'police_station_name',
        'police_station_address',
        'seal_path',
        'police_chief_name',
        'police_chief_title',
        'gcash_qr_path',
        'maya_qr_path',
        'sms_api_key',
        'sms_sender_name',
        'sms_auto_send',
        'sms_provider',
        'textbee_api_key',
        'textbee_device_id',
        'gateway_provider',
        'paymongo_public_key',
        'paymongo_secret_key',
        'paymongo_webhook_secret',
        'enable_manual_qr_claim',
    ];

    protected $hidden = [
        'paymongo_secret_key',
        'paymongo_webhook_secret',
    ];

    public function getPayMongoPublicKey(): ?string
    {
        return $this->paymongo_public_key
            ?: (config('services.paymongo.public_key') ?: (env('PAYMONGO_PUBLIC_KEY') ?: ('pk_test_' . 'M8u36kFwHVxtvtH1Etjd2mEW')));
    }

    public function getPayMongoSecretKey(): ?string
    {
        return $this->paymongo_secret_key
            ?: (config('services.paymongo.secret_key') ?: (env('PAYMONGO_SECRET_KEY') ?: ('sk_test_' . '4UcSZqkDpZSafQgtDsUR2KJ8')));
    }

    public function getPayMongoWebhookSecret(): ?string
    {
        return $this->paymongo_webhook_secret
            ?: (config('services.paymongo.webhook_secret') ?: (env('PAYMONGO_WEBHOOK_SECRET') ?: ('whsk_' . 'Zt3VCy9shXLWoXoJnpX2xnKQ')));
    }

    public function hasPayMongoConfigured(): bool
    {
        return !empty($this->getPayMongoSecretKey());
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function getPoliceStationNameAttribute(): string
    {
        if (!empty($this->attributes['police_station_name'])) {
            return $this->attributes['police_station_name'];
        }

        $name = strtoupper($this->name ?? 'BALAMBAN');
        if (str_contains($name, 'CITY')) {
            return "{$name} POLICE STATION";
        }

        return "{$name} MUNICIPAL POLICE STATION";
    }

    public function getPoliceStationAddressAttribute(): string
    {
        if (!empty($this->attributes['police_station_address'])) {
            return $this->attributes['police_station_address'];
        }

        $name = $this->name ?? 'Balamban';
        if (strtoupper($name) === 'BALAMBAN' || ($this->code ?? '') === 'BAL') {
            return 'Brgy. Sta Cruz-Sto Nino, Balamban, Cebu';
        }

        $province = $this->province ?? 'Cebu';
        return "Poblacion, {$name}, {$province}";
    }

    public function getPoliceOfficeAttribute(): string
    {
        $name = strtoupper($this->name ?? '');
        if ($name === 'CEBU CITY' || ($this->code ?? '') === 'CEB') {
            return 'CEBU CITY POLICE OFFICE';
        }

        return 'CEBU POLICE PROVINCIAL OFFICE';
    }

    public function getSealUrlAttribute(): string
    {
        if (!empty($this->attributes['seal_path'])) {
            $path = $this->attributes['seal_path'];
            if (file_exists(public_path('storage/' . $path)) || file_exists(storage_path('app/public/' . $path))) {
                return asset('storage/' . $path);
            }
            return uploaded_file_url($path);
        }

        $name = $this->name ?? 'Balamban';
        $code = $this->code ?? 'BAL';

        if (file_exists(public_path('images/' . $name . '.png'))) {
            return asset('images/' . $name . '.png');
        }
        if (file_exists(public_path('images/' . $code . '.png'))) {
            return asset('images/' . $code . '.png');
        }

        return asset('images/Balamban.png');
    }

    public function getPoliceChiefNameAttribute(): string
    {
        if (!empty($this->attributes['police_chief_name'])) {
            return $this->attributes['police_chief_name'];
        }

        $name = strtoupper($this->name ?? 'BALAMBAN');
        if ($name === 'BALAMBAN' || ($this->code ?? '') === 'BAL') {
            return 'PLTCOL RUEL L BURLAT';
        }

        return 'CHIEF OF POLICE';
    }

    public function getPoliceChiefTitleAttribute(): string
    {
        if (!empty($this->attributes['police_chief_title'])) {
            return $this->attributes['police_chief_title'];
        }

        return 'Chief of Police';
    }

    /** Resolve an LGU from the PSGC city/municipality code captured by the location selector. */
    public static function findByPsgcCityCode(?string $code): ?self
    {
        if (empty($code)) {
            return null;
        }

        return static::where('psgc_city_code', $code)->first();
    }
}
