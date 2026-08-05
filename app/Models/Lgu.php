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
        return $this->paymongo_public_key ?: config('services.paymongo.public_key', env('PAYMONGO_PUBLIC_KEY'));
    }

    public function getPayMongoSecretKey(): ?string
    {
        return $this->paymongo_secret_key ?: config('services.paymongo.secret_key', env('PAYMONGO_SECRET_KEY'));
    }

    public function getPayMongoWebhookSecret(): ?string
    {
        return $this->paymongo_webhook_secret ?: config('services.paymongo.webhook_secret', env('PAYMONGO_WEBHOOK_SECRET'));
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

    /** Resolve an LGU from the PSGC city/municipality code captured by the location selector. */
    public static function findByPsgcCityCode(?string $code): ?self
    {
        if (empty($code)) {
            return null;
        }

        return static::where('psgc_city_code', $code)->first();
    }
}
