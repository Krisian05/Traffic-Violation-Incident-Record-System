<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PaymentsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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
            (float) $payment->amount_paid,
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
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803D']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $totalRow = $highestRow + 1;

                // Write "Total Collection" title label in Column A
                $sheet->setCellValue("A{$totalRow}", 'Total Collection');

                // Write SUM formula in Column B for Amount Paid
                if ($highestRow >= 2) {
                    $sheet->setCellValue("B{$totalRow}", "=SUM(B2:B{$highestRow})");
                } else {
                    $sheet->setCellValue("B{$totalRow}", 0);
                }

                // Format Column B (Amount Paid) as currency / 2 decimal places
                $sheet->getStyle("B2:B{$totalRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                // Apply prominent styling to the Total Collection summary row across columns A through H
                $sheet->getStyle("A{$totalRow}:H{$totalRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '15803D'],
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F0FDF4'],
                    ],
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '15803D'],
                        ],
                        'bottom' => [
                            'borderStyle' => Border::BORDER_DOUBLE,
                            'color' => ['rgb' => '15803D'],
                        ],
                    ],
                ]);
            },
        ];
    }
}

