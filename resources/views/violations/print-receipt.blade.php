<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <title>Official Receipt #{{ $payment->or_number }} — TVIRS</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #1c1917;
            background: #f5f5f4;
            padding: 20px;
        }
        @page { size: 100mm 150mm; margin: 5mm; }
        .receipt-card {
            max-width: 400px;
            margin: 0 auto;
            background: #fff;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 1px solid #e7e2db;
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #d6d3d1;
            padding-bottom: 16px;
            margin-bottom: 16px;
        }
        .agency-title { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #78716c; letter-spacing: 0.05em; }
        .lgu-name { font-size: 16px; font-weight: 900; color: #1c1917; margin-top: 2px; }
        .receipt-title { font-size: 13px; font-weight: 800; color: #15803d; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 8px; }
        .or-number { font-size: 20px; font-weight: 900; font-family: ui-monospace, monospace; color: #1c1917; margin-top: 4px; }
        .info-table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 16px; }
        .info-table td { padding: 5px 0; border-bottom: 1px dashed #f5f0e8; }
        .lbl { color: #78716c; font-weight: 600; }
        .val { font-weight: 700; text-align: right; color: #1c1917; }
        .amount-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            margin-bottom: 16px;
        }
        .amount-lbl { font-size: 10px; font-weight: 700; color: #166534; text-transform: uppercase; letter-spacing: 0.05em; }
        .amount-val { font-size: 24px; font-weight: 900; color: #15803d; }
        .footer { text-align: center; font-size: 10px; color: #a8a29e; border-top: 2px dashed #d6d3d1; pt-12; padding-top: 12px; }
        @media print {
            body { background: #fff; padding: 0; }
            .receipt-card { box-shadow: none; border: none; width: 100%; max-width: none; padding: 0; }
        }
    </style>
</head>
<body>

@php
    $lgu = $violation->lgu ?? $violation->recorder?->lgu ?? auth()->user()?->lgu ?? \App\Models\Lgu::where('code', 'BAL')->first();
@endphp
<div class="receipt-card">
    <div class="header">
        <div class="agency-title">Republic of the Philippines</div>
        <div class="lgu-name">{{ strtoupper($lgu?->name ?? 'BALAMBAN') }} {{ str_contains(strtolower($lgu?->name ?? ''), 'city') ? 'CITY' : 'MUNICIPALITY' }}</div>
        <div style="font-size: 10px; color: #78716c;">{{ strtoupper($lgu?->treasurer_office ?? 'OFFICE OF THE MUNICIPAL TREASURER') }}</div>
        <div class="receipt-title">Official Receipt</div>
        <div class="or-number">OR-{{ $payment->or_number }}</div>
        @if($payment->isVoided())
            <div style="background: #fef2f2; color: #b91c1c; font-weight: 900; border: 2px solid #fca5a5; border-radius: 6px; padding: 4px; margin-top: 8px; font-size: 14px; text-transform: uppercase;">
                *** VOIDED PAYMENT ***
            </div>
        @endif
    </div>

    <div class="amount-box">
        <div class="amount-lbl">Amount Paid</div>
        <div class="amount-val">₱{{ number_format($payment->amount_paid, 2) }}</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="lbl">Date & Time Paid</td>
            <td class="val">{{ $payment->paid_at->format('M d, Y g:i A') }}</td>
        </tr>
        <tr>
            <td class="lbl">Ticket Number</td>
            <td class="val" style="font-family: ui-monospace, monospace;">{{ $violation->ticket_number ?: '#' . $violation->id }}</td>
        </tr>
        <tr>
            <td class="lbl">Violator Name</td>
            <td class="val">{{ $violation->violator?->full_name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Violation Type</td>
            <td class="val">{{ $violation->violationType?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Payment Method</td>
            <td class="val" style="text-transform: uppercase;">{{ $payment->payment_method }}</td>
        </tr>
        <tr>
            <td class="lbl">Cashier / Collector</td>
            <td class="val">{{ $payment->cashier_name }}</td>
        </tr>
        <tr>
            <td class="lbl">Remaining Balance</td>
            <td class="val" style="color: {{ $violation->balanceRemaining() > 0 ? '#b91c1c' : '#15803d' }};">
                ₱{{ number_format($violation->balanceRemaining(), 2) }}
            </td>
        </tr>
    </table>

    @if($payment->isVoided())
        <div style="font-size: 11px; color: #7f1d1d; background: #fef2f2; padding: 8px; border-radius: 6px; margin-bottom: 16px;">
            <strong>Void Reason:</strong> {{ $payment->void_reason }}<br>
            <strong>Voided By:</strong> {{ $payment->voidedByUser?->name ?? 'System' }} on {{ $payment->voided_at?->format('M d, Y g:i A') }}
        </div>
    @endif

    <div class="footer">
        <p>This serves as an official electronic payment receipt.</p>
        <p style="margin-top: 4px;">TVIRS Traffic Violation Incident Record System</p>
    </div>
</div>

<script>
    window.addEventListener('load', function() {
        setTimeout(function() { window.print(); }, 500);
    });
</script>
</body>
</html>
