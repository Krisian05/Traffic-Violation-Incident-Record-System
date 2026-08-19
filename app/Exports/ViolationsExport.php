<?php

namespace App\Exports;

use App\Models\Violation;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ViolationsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    use Exportable;

    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Violation::with(['violator', 'violationType', 'vehicle', 'payments', 'lgu'])
            ->leftJoin('violators', 'violations.violator_id', '=', 'violators.id')
            ->leftJoin('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
            ->select('violations.*');

        $year = (int) ($this->filters['year'] ?? now()->year);
        $query->whereYear('violations.date_of_violation', $year);

        if (!empty($this->filters['month']) && $this->filters['month'] != 0) {
            $query->whereMonth('violations.date_of_violation', $this->filters['month']);
        }

        if (!empty($this->filters['search'])) {
            $lk = '%' . mb_strtolower($this->filters['search']) . '%';
            $query->where(function ($q) use ($lk) {
                $q->whereRaw('LOWER(violators.first_name) LIKE ?', [$lk])
                  ->orWhereRaw('LOWER(violators.last_name) LIKE ?', [$lk])
                  ->orWhereRaw('LOWER(violators.middle_name) LIKE ?', [$lk]);
            });
        }

        if (!empty($this->filters['type_filter'])) {
            $query->where('violations.violation_type_id', $this->filters['type_filter']);
        }

        if (!empty($this->filters['lgu_id'])) {
            $query->where('violations.lgu_id', $this->filters['lgu_id']);
        } elseif (!empty($this->filters['municipality'])) {
            $query->where('violations.location', 'ilike', '%' . $this->filters['municipality'] . '%');
        }

        return $query->orderBy('violations.date_of_violation', 'desc');
    }

    public function headings(): array
    {
        return [
            'Ticket Number',
            'LGU / Municipality',
            'Violator Name',
            'License Number',
            'Plate Number',
            'Violation Type',
            'Fine Amount',
            'Balance Due',
            'Location',
            'Date of Violation',
            'Due Date',
            'Status',
            'OR Number',
            'Cashier Name',
            'Payment Method',
            'Date Settled',
        ];
    }

    public function map($violation): array
    {
        $isOverdue = $violation->isOverdue();
        $status = $isOverdue ? 'Overdue' : ucfirst($violation->status);

        return [
            $violation->ticket_number ?: '#' . $violation->id,
            $violation->lgu?->name ?? '—',
            $violation->violator?->full_name ?? '—',
            $violation->violator?->license_number ?? '—',
            $violation->vehicle?->plate_number ?? $violation->vehicle_plate ?? '—',
            $violation->violationType?->name ?? '—',
            !is_null($violation->fine_amount) ? (float)$violation->fine_amount : ($violation->violationType?->fine_amount ?: 0),
            $violation->balanceRemaining(),
            $violation->location ?: '—',
            $violation->date_of_violation->format('Y-m-d'),
            $violation->due_date?->format('Y-m-d') ?: '—',
            $status,
            $violation->or_number ?: '—',
            $violation->cashier_name ?: '—',
            $violation->payment_method ?: '—',
            $violation->settled_at ? $violation->settled_at->format('Y-m-d H:i:s') : '—',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1d4ed8']]],
        ];
    }
}
