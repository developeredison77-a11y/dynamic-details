<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} Report</title>
    <style>
        :root { color-scheme: light; }
        @page { size: A4 landscape; margin: 12mm; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f3f6fb; color: #111827; font-family: Arial, sans-serif; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .print-actions { display: flex; justify-content: flex-end; max-width: 1120px; margin: 20px auto 10px; }
        .print-actions button { min-height: 40px; border: 1px solid #1d4ed8; border-radius: 8px; background: #2563eb; color: #fff; cursor: pointer; font-weight: 700; padding: 0 18px; }
        .document { max-width: 1120px; margin: 0 auto 30px; border: 1px solid #d8e0ec; border-radius: 10px; background: #fff; overflow: hidden; }
        .document-header { display: flex; justify-content: space-between; gap: 24px; border-bottom: 3px solid #1f3b66; padding: 24px 28px; }
        .eyebrow, th, .metric span { color: #64748b; font-size: 11px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; }
        h1 { margin: 6px 0 0; color: #0f172a; font-size: 26px; line-height: 1.15; }
        .document-number { min-width: 190px; border: 1px solid #d8e0ec; border-radius: 8px; background: #f8fafc; padding: 14px; text-align: right; }
        .document-number strong { display: block; margin-top: 5px; font-size: 16px; }
        .content { display: grid; gap: 18px; padding: 22px 28px 28px; }
        .metric-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; }
        .metric { border: 1px solid #d8e0ec; border-radius: 8px; background: #f8fafc; padding: 14px; }
        .metric strong { display: block; margin-top: 8px; color: #0f172a; font-size: 24px; }
        .status-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
        .section { border: 1px solid #d8e0ec; border-radius: 8px; overflow: hidden; }
        .section-title { border-bottom: 1px solid #d8e0ec; background: #f8fafc; color: #0f172a; font-weight: 800; padding: 12px 14px; }
        .status-row { display: flex; justify-content: space-between; gap: 12px; border-bottom: 1px solid #eef2f7; padding: 10px 14px; }
        .status-row:last-child { border-bottom: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #e5ebf3; padding: 10px 12px; text-align: left; vertical-align: top; }
        td { color: #111827; font-size: 12px; }
        tr:last-child td { border-bottom: 0; }
        @media print {
            body { background: #fff; }
            .print-actions { display: none; }
            .document { margin: 0; max-width: none; border: 0; border-radius: 0; }
            .document-header { padding: 0 0 16px; }
            .content { padding: 16px 0 0; }
            .section, .metric { break-inside: avoid; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="print-actions"><button type="button" onclick="window.print()">Download PDF</button></div>
    <main class="document">
        <header class="document-header">
            <div>
                <span class="eyebrow">ADMS Report</span>
                <h1>{{ $title }}</h1>
            </div>
            <div class="document-number">
                <span class="eyebrow">Generated</span>
                <strong>{{ now(config('app.timezone', 'Asia/Kolkata'))->format('M d, Y h:i A') }}</strong>
            </div>
        </header>

        <section class="content">
            @if ($type === 'overview')
                <div class="metric-grid">
                    <article class="metric"><span>Total Assets</span><strong>{{ $summary['assets'] }}</strong></article>
                    <article class="metric"><span>Employees</span><strong>{{ $summary['employees'] }}</strong></article>
                    <article class="metric"><span>Active Handovers</span><strong>{{ $summary['activeHandovers'] }}</strong></article>
                    <article class="metric"><span>Returns</span><strong>{{ $summary['returns'] }}</strong></article>
                    <article class="metric"><span>Declarations</span><strong>{{ $summary['declarations'] }}</strong></article>
                </div>

                <div class="status-grid">
                    <article class="section">
                        <div class="section-title">Asset Status</div>
                        @forelse($assetsByStatus as $status => $total)
                            <div class="status-row"><span>{{ ucfirst($status) }}</span><strong>{{ $total }}</strong></div>
                        @empty
                            <div class="status-row"><span>No asset data</span><strong>0</strong></div>
                        @endforelse
                    </article>
                    <article class="section">
                        <div class="section-title">Employee Status</div>
                        @forelse($employeesByStatus as $status => $total)
                            <div class="status-row"><span>{{ ucfirst($status) }}</span><strong>{{ $total }}</strong></div>
                        @empty
                            <div class="status-row"><span>No employee data</span><strong>0</strong></div>
                        @endforelse
                    </article>
                </div>
            @else
                <article class="section">
                    <div class="section-title">{{ $title }}</div>
                    <table>
                        <thead>
                            <tr>
                                @foreach(array_keys($rows[0] ?? []) as $heading)
                                    <th>{{ $heading }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                                <tr>
                                    @foreach($row as $value)
                                        <td>{{ $value ?: '-' }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr><td>No report data found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </article>
            @endif
        </section>
    </main>
</body>
</html>
