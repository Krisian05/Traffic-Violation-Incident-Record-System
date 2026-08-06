<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>TVIRS Report Summary</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            border-bottom: 2px double #b91c1c;
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .header h2 {
            margin: 0 0 5px 0;
            color: #111;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            color: #555;
            font-size: 10px;
        }
        .report-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #b91c1c;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 5px;
            border-bottom: 1px solid #ddd;
        }
        .meta-label {
            font-weight: bold;
            color: #555;
        }
        .kpi-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }
        .kpi-box {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            background-color: #faf9f6;
        }
        .kpi-num {
            font-size: 18px;
            font-weight: bold;
            color: #1d4ed8;
            margin-top: 5px;
        }
        .kpi-num.red {
            color: #b91c1c;
        }
        .kpi-num.green {
            color: #15803d;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #fff;
            background-color: #dc2626;
            padding: 5px 10px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .data-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            border-bottom: 2px solid #ddd;
            font-size: 10px;
        }
        .data-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            font-size: 10px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #aaa;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>{{ strtoupper($lgu?->police_office ?? 'Cebu Police Provincial Office') }}</h2>
        <p>{{ strtoupper($lgu?->police_station_name ?? ($lguName ? ($lguName . ' Police Station') : 'Province of Cebu Traffic Command (All LGUs)')) }}</p>
        <p style="font-size: 8px; color: #888;">Traffic Violation Incident Record System (TVIRS)</p>
    </div>

    <div class="report-title">
        System Summary Report
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label" style="width: 25%;">Report Scope</td>
            <td style="width: 25%;">{{ $lguName ?: 'All LGUs (Province-Wide)' }}</td>
            <td class="meta-label" style="width: 25%;">Report Period</td>
            <td style="width: 25%;">{{ $periodLabel }}</td>
        </tr>
        <tr>
            <td class="meta-label">Generated On</td>
            <td>{{ now()->format('M d, Y  h:i A') }}</td>
            <td class="meta-label">Type Filter</td>
            <td>{{ $typeFilterName }}</td>
        </tr>
    </table>

    <div class="section-title">Enforcement Overview</div>
    <table class="kpi-table">
        <tr>
            <td style="width: 33.3%;">
                <div class="kpi-box">
                    <div style="font-weight: bold; color: #555;">Total Violations</div>
                    <div class="kpi-num">{{ $totalViolationsCount }}</div>
                </div>
            </td>
            <td style="width: 33.3%;">
                <div class="kpi-box">
                    <div style="font-weight: bold; color: #555;">Settled Violations</div>
                    <div class="kpi-num green">{{ $settledCount }}</div>
                </div>
            </td>
            <td style="width: 33.3%;">
                <div class="kpi-box">
                    <div style="font-weight: bold; color: #555;">Overdue Fine Tags</div>
                    <div class="kpi-num red">{{ $overdueCount }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Violation Categories Summary</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 70%;">Violation Type</th>
                <th style="width: 30%; text-align: right;">Count</th>
            </tr>
        </thead>
        <tbody>
            @forelse($violationsByType as $typeName => $total)
                <tr>
                    <td>{{ $typeName }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ $total }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" style="text-align: center; color: #888;">No violations recorded in this category.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Location Hotspots</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 70%;">Location / Street</th>
                <th style="width: 30%; text-align: right;">Total Citations</th>
            </tr>
        </thead>
        <tbody>
            @forelse($violationHotspots as $h)
                <tr>
                    <td>{{ $h->location }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ $h->total }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" style="text-align: center; color: #888;">No location data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 40px; margin-bottom: 20px; width: 100%;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; border: none; vertical-align: top;">
                    <div>Prepared by:</div>
                    <div style="border-bottom: 1px solid #000; width: 180px; margin-top: 30px;"></div>
                    <div style="font-size: 10px; font-weight: bold; margin-top: 3px;">{{ Auth::user()->name ?? 'System Administrator' }}</div>
                    <div style="font-size: 8px; font-style: italic;">Operation PNCO</div>
                </td>
                <td style="width: 50%; border: none; vertical-align: top; text-align: right;">
                    <div style="display: inline-block; text-align: left;">
                        <div>Noted by:</div>
                        <div style="border-bottom: 1px solid #000; width: 180px; margin-top: 30px;"></div>
                        <div style="font-size: 10px; font-weight: bold; margin-top: 3px;">{{ $lgu?->police_chief_name ?? 'PLTCOL RUEL L BURLAT' }}</div>
                        <div style="font-size: 8px; font-style: italic;">{{ $lgu?->police_chief_title ?? 'Chief of Police' }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Generated automatically by the TVIRS System. Page 1 of 1.
    </div>

</body>
</html>
