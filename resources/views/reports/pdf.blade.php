<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reports</title>
    <style>
        @page { margin: 28px 28px; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 10px; }
        .meta { font-size: 11px; color: #666; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        thead th { background: #f3f4f6; text-align: left; border-bottom: 1px solid #e5e7eb; padding: 8px; }
        tbody td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        tfoot td { padding: 8px; border-top: 1px solid #e5e7eb; font-weight: bold; }
        .right { text-align: right; }
        .sl { width: 36px; }
        .date { width: 110px; }
        .type { width: 90px; text-transform: capitalize; }
    </style>
</head>
<body>
    <h1>Reports</h1>
    <div class="meta">
        @php($range = trim((!empty($start) ? \Carbon\Carbon::parse($start)->format('d-M - Y') : '—'). ' to ' . (!empty($end) ? \Carbon\Carbon::parse($end)->format('d-M - Y') : '—')))
        <strong>Date Range:</strong> {{ $range }}<br>
        <strong>Types:</strong> {{ implode(', ', array_map(fn($t)=>str_replace('_',' ',$t), $types ?? [])) }}
    </div>

    <table>
        <thead>
            <tr>
                <th class="sl">SL</th>
                <th class="date">Date</th>
                <th class="type">Type</th>
                <th>Label</th>
                <th class="right">In</th>
                <th class="right">Out</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $row)
                <tr>
                    <td class="sl">{{ $loop->iteration }}</td>
                    <td class="date">{{ \Carbon\Carbon::parse($row['date'])->format('d-M - Y') }}</td>
                    <td class="type">{{ str_replace('_',' ', $row['type']) }}</td>
                    <td>{{ $row['label'] }}</td>
                    <td class="right">{{ number_format($row['in'], 2) }}</td>
                    <td class="right">{{ number_format($row['out'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="right">No data</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td colspan="3" class="right">Totals</td>
                <td class="right">{{ number_format($totalIn, 2) }}</td>
                <td class="right">{{ number_format($totalOut, 2) }}</td>
            </tr>
            <tr>
                <td></td>
                <td colspan="3" class="right">Balance</td>
                <td class="right" colspan="2">{{ number_format($balance, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    <div style="margin-top: 12px; font-size: 11px; color:#666; text-align:right;">
        Exported on: {{ now()->format('d-M - Y H:i') }}
    </div>
</body>
</html>
