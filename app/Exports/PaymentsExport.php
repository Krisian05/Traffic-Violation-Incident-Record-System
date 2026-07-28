<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    use Exportable;

    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        return Payment::with(['violation.violator', 'violation.lgu', 'collector'])
            ->when(!empty($this->filters['lgu_id']), fn($q) => $q->whereHas('violation', fn($sq) => $sq->where('lgu_id', $this->filters['lgu_id'])))
            ->when(!empty($this->filters['date_from']), fn($q) => $q->whereDate('paid_at', '>=', $this->filters['date_from']))
            ->when(!empty($this->filters['date_to']), fn($q) => $q->whereDate('paid_at', '<=', $this->filters['date_to']))
            ->when(!empty($this->filters['method']), fn($q) => $q->where('payment_method', $this->filters['method']))
            ->when(!empty($this->filters['or_number']), fn($q) => $q->where('or_number', 'like', '%' . str_replace(['%', '_'], ['\%', '\_'], $this->filters['or_number']) . '%'))
            ->orderByDesc('paid_at');
    }

    public function headings(): array
    {
        return [
            'OR Number',
            'Amount Paid',
            'Payment Method',
            'Date Paid',
            'Cashier / Collector',
            'LGU',
            'Ticket Number',
            'Violator',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->or_number,
            $payment->amount_paid,
            ucfirst($payment->payment_method),
            $payment->paid_at->format('Y-m-d H:i:s'),
            $payment->collector?->name ?? $payment->cashier_name,
            $payment->violation?->lgu?->name ?? '—',
            $payment->violation?->ticket_number ?? ('#' . $payment->violation_id),
            $payment->violation?->violator?->full_name ?? '—',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '15803d']]],
        ];
    }
}
