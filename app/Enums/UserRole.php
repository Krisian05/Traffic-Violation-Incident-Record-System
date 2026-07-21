<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Operator = 'operator';
    case TrafficOfficer = 'traffic_officer';
    case ProvinceAdmin = 'province_admin';
    case Cashier = 'cashier';
    case Auditor = 'auditor';

    public static function values(): array
    {
        return array_map(fn (self $role) => $role->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin — Full Access + User Management',
            self::Operator => 'Operator — Full Access (except User Management)',
            self::TrafficOfficer => 'Traffic Officer — Mobile',
            self::ProvinceAdmin => 'Province Admin — Province-wide Monitoring',
            self::Cashier => 'Cashier — Payment Collection',
            self::Auditor => 'Auditor — Read-only Reports & Logs',
        };
    }

    /**
     * Roles that are tied to a single LGU. Admin and Province Admin operate
     * across all LGUs, so they don't require an lgu_id assignment.
     */
    public static function lguScopedValues(): array
    {
        return [
            self::Operator->value,
            self::TrafficOfficer->value,
            self::Cashier->value,
            self::Auditor->value,
        ];
    }
}
