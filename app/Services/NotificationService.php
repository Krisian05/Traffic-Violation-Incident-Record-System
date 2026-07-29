<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Payment;
use App\Models\User;
use App\Models\Violation;
use App\Models\Incident;
use App\Models\Violator;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Resolve notification recipients scoped to a record's LGU.
     * admin/province_admin see across all LGUs; everyone else only sees their own LGU.
     */
    private function usersForRoles(array $roles, ?int $lguId = null)
    {
        return User::whereIn('role', $roles)
            ->when($lguId, function ($query) use ($lguId) {
                $query->where(function ($sub) use ($lguId) {
                    $sub->whereIn('role', ['admin', 'province_admin'])
                        ->orWhere('lgu_id', $lguId);
                });
            })
            ->get();
    }

    /**
     * Broadcast notification when an enforcer/officer records a new violation.
     */
    public function notifyNewViolation(Violation $violation): void
    {
        $targetRoles = ['admin', 'province_admin', 'treasurer', 'operator', 'traffic_supervisor'];
        $users = $this->usersForRoles($targetRoles, $violation->lgu_id);

        $violatorName = $violation->violator ? $violation->violator->full_name : 'Unknown Violator';
        $fineAmount = $violation->violationType ? number_format($violation->violationType->fine_amount, 2) : '0.00';
        $ticketNo = $violation->ticket_number ?: '#' . $violation->id;

        foreach ($users as $user) {
            Notification::create([
                'id'              => (string) Str::uuid(),
                'type'            => 'violation_created',
                'notifiable_type' => User::class,
                'notifiable_id'   => $user->id,
                'data'            => [
                    'title'       => 'New Traffic Violation Recorded',
                    'message'     => "Ticket {$ticketNo} issued to {$violatorName} (₱{$fineAmount})",
                    'icon'        => 'bi-ticket-perforated-fill',
                    'color'       => '#dc2626',
                    'bg'          => '#fef2f2',
                    'url'         => route('violations.show', $violation->id),
                    'violation_id'=> $violation->id,
                ],
            ]);
        }
    }

    /**
     * Broadcast notification when a cashier collects a payment.
     */
    public function notifyPaymentSettled(Violation $violation, Payment $payment): void
    {
        $targetRoles = ['admin', 'province_admin', 'treasurer', 'auditor', 'operator'];
        $users = $this->usersForRoles($targetRoles, $violation->lgu_id);

        $ticketNo = $violation->ticket_number ?: '#' . $violation->id;
        $amountPaid = number_format($payment->amount_paid, 2);
        $orNo = $payment->or_number;
        $cashierName = $payment->cashier_name ?: 'Cashier';

        foreach ($users as $user) {
            Notification::create([
                'id'              => (string) Str::uuid(),
                'type'            => 'payment_settled',
                'notifiable_type' => User::class,
                'notifiable_id'   => $user->id,
                'data'            => [
                    'title'       => 'Payment Received by Cashier',
                    'message'     => "₱{$amountPaid} collected for Ticket {$ticketNo} (OR: {$orNo}) by {$cashierName}",
                    'icon'        => 'bi-cash-coin',
                    'color'       => '#16a34a',
                    'bg'          => '#f0fdf4',
                    'url'         => route('violations.cashier') . '?search=' . urlencode($ticketNo),
                    'payment_id'  => $payment->id,
                ],
            ]);
        }
    }

    /**
     * Broadcast notification when a traffic incident is logged.
     */
    public function notifyIncidentLogged(Incident $incident): void
    {
        $targetRoles = ['admin', 'province_admin', 'traffic_officer', 'traffic_supervisor', 'operator'];
        $users = $this->usersForRoles($targetRoles, $incident->lgu_id);

        $incidentNo = $incident->incident_number ?: '#' . $incident->id;
        $location = $incident->location ?: 'Recorded Location';

        foreach ($users as $user) {
            Notification::create([
                'id'              => (string) Str::uuid(),
                'type'            => 'incident_logged',
                'notifiable_type' => User::class,
                'notifiable_id'   => $user->id,
                'data'            => [
                    'title'       => 'Traffic Incident Reported',
                    'message'     => "Incident {$incidentNo} reported at {$location}",
                    'icon'        => 'bi-exclamation-triangle-fill',
                    'color'       => '#d97706',
                    'bg'          => '#fffbeb',
                    'url'         => route('incidents.show', $incident->id),
                    'incident_id' => $incident->id,
                ],
            ]);
        }
    }

    /**
     * Broadcast notification when a Data Subject Privacy request is submitted.
     */
    public function notifyDsrSubmitted(array $dsrData): void
    {
        $targetRoles = ['admin', 'province_admin', 'operator', 'auditor'];

        $lguId = null;
        if (!empty($dsrData['ticket_number'])) {
            $lguId = Violation::where('ticket_number', $dsrData['ticket_number'])->value('lgu_id');
        }
        if (!$lguId && !empty($dsrData['license_number'])) {
            $lguId = Violator::where('license_number', $dsrData['license_number'])->value('lgu_id');
        }

        $users = $this->usersForRoles($targetRoles, $lguId);

        $name = $dsrData['full_name'] ?? 'Public User';
        $type = ucfirst($dsrData['request_type'] ?? 'privacy');

        foreach ($users as $user) {
            Notification::create([
                'id'              => (string) Str::uuid(),
                'type'            => 'dsr_submitted',
                'notifiable_type' => User::class,
                'notifiable_id'   => $user->id,
                'data'            => [
                    'title'       => 'Data Subject Privacy Request',
                    'message'     => "{$type} request submitted by {$name}",
                    'icon'        => 'bi-shield-lock-fill',
                    'color'       => '#2563eb',
                    'bg'          => '#eff6ff',
                    'url'         => route('audit-logs.index') . '?search=' . urlencode($name),
                ],
            ]);
        }
    }

    /**
     * Broadcast notification when a new motorist profile is created by an enforcer or staff.
     */
    public function notifyNewMotorist(Violator $violator): void
    {
        $targetRoles = ['admin', 'province_admin', 'operator', 'traffic_officer', 'traffic_supervisor', 'records_officer'];
        $users = $this->usersForRoles($targetRoles, $violator->lgu_id);

        $name = $violator->full_name;
        $license = $violator->license_number ? " (License: {$violator->license_number})" : '';

        foreach ($users as $user) {
            Notification::create([
                'id'              => (string) Str::uuid(),
                'type'            => 'motorist_registered',
                'notifiable_type' => User::class,
                'notifiable_id'   => $user->id,
                'data'            => [
                    'title'       => 'New Motorist Profile Registered',
                    'message'     => "New motorist profile created for {$name}{$license}",
                    'icon'        => 'bi-person-fill-add',
                    'color'       => '#2563eb',
                    'bg'          => '#eff6ff',
                    'url'         => route('violators.show', $violator->id),
                    'violator_id' => $violator->id,
                ],
            ]);
        }
    }
}
