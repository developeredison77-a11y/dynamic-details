<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Asset Handover Form #{{ $assignment->id }}</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f3f6fb; color: #111827; font-family: Arial, sans-serif; }
        .print-actions { display: flex; justify-content: flex-end; max-width: 920px; margin: 24px auto 12px; }
        .print-actions button { min-height: 40px; border: 1px solid #1d4ed8; border-radius: 8px; background: #2563eb; color: #fff; cursor: pointer; font-weight: 700; padding: 0 18px; }
        .document { max-width: 920px; margin: 0 auto 36px; border: 1px solid #d8e0ec; border-radius: 10px; background: #fff; overflow: hidden; }
        .document-header { display: flex; justify-content: space-between; gap: 24px; border-bottom: 3px solid #1f3b66; padding: 28px 32px 24px; }
        .eyebrow { color: #64748b; font-size: 12px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        h1 { margin: 6px 0 0; color: #0f172a; font-size: 28px; line-height: 1.15; }
        .document-number { min-width: 170px; border: 1px solid #d8e0ec; border-radius: 8px; background: #f8fafc; padding: 14px; text-align: right; }
        .document-number strong { display: block; margin-top: 5px; font-size: 20px; }
        .summary-strip { display: grid; grid-template-columns: repeat(3, 1fr); border-bottom: 1px solid #d8e0ec; background: #f8fafc; }
        .summary-strip div { border-right: 1px solid #d8e0ec; padding: 16px 22px; }
        .summary-strip div:last-child { border-right: 0; }
        .summary-strip span, .section-title, dt { color: #64748b; font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
        .summary-strip strong { display: block; margin-top: 5px; color: #0f172a; font-size: 16px; }
        .content { display: grid; gap: 22px; padding: 26px 32px 32px; }
        .two-column { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .section { border: 1px solid #d8e0ec; border-radius: 8px; padding: 18px; }
        .section h2 { margin: 6px 0 16px; color: #0f172a; font-size: 18px; }
        dl { display: grid; gap: 10px; margin: 0; }
        dl div { display: flex; justify-content: space-between; gap: 16px; border-bottom: 1px solid #eef2f7; padding-bottom: 8px; }
        dl div:last-child { border-bottom: 0; padding-bottom: 0; }
        dd { margin: 0; color: #111827; font-weight: 700; text-align: right; }
        .notes { min-height: 80px; color: #334155; line-height: 1.6; }
        .terms { color: #334155; line-height: 1.7; }
        .signature-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 42px; margin-top: 28px; }
        .signature-box { padding-top: 56px; }
        .signature-line { border-top: 1px solid #111827; padding-top: 10px; color: #334155; font-weight: 700; }
        @media print {
            body { background: #fff; }
            .print-actions { display: none; }
            .document { margin: 0; max-width: none; border: 0; border-radius: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="print-actions"><button type="button" onclick="window.print()">Print</button></div>
    <main class="document">
        <header class="document-header">
            <div>
                <span class="eyebrow">Asset Handover</span>
                <h1>{{ $assignment->asset?->asset_tag }} to {{ $assignment->employee?->name_en }}</h1>
            </div>
            <div class="document-number">
                <span class="eyebrow">Reference</span>
                <strong>#{{ $assignment->id }}</strong>
            </div>
        </header>

        <section class="summary-strip">
            <div><span>Handover Date</span><strong>{{ $assignment->handover_date?->format('M d, Y') }}</strong></div>
            <div><span>Expected Return</span><strong>{{ $assignment->expected_return_date?->format('M d, Y') ?: '-' }}</strong></div>
            <div><span>Status</span><strong>{{ $assignment->status?->label() }}</strong></div>
        </section>

        <section class="content">
            <div class="two-column">
                <article class="section">
                    <span class="section-title">Employee</span>
                    <h2>{{ $assignment->employee?->name_en }}</h2>
                    <dl>
                        <div><dt>Code</dt><dd>{{ $assignment->employee?->employee_code }}</dd></div>
                        <div><dt>Arabic Name</dt><dd dir="rtl">{{ $assignment->employee?->name_ar ?: '-' }}</dd></div>
                    </dl>
                </article>

                <article class="section">
                    <span class="section-title">Asset</span>
                    <h2>{{ $assignment->asset?->name }}</h2>
                    <dl>
                        <div><dt>Tag</dt><dd>{{ $assignment->asset?->asset_tag }}</dd></div>
                        <div><dt>Brand</dt><dd>{{ $assignment->asset?->brand?->name ?? '-' }}</dd></div>
                        <div><dt>Category</dt><dd>{{ $assignment->asset?->category?->name ?? '-' }}</dd></div>
                        <div><dt>Serial</dt><dd>{{ $assignment->asset?->serial_number ?: '-' }}</dd></div>
                    </dl>
                </article>
            </div>

            <article class="section">
                <span class="section-title">Handover Notes</span>
                <p class="notes">{{ $assignment->handover_notes ?: 'No handover notes recorded.' }}</p>
            </article>

            <article class="section">
                <span class="section-title">Responsibility Terms</span>
                <p class="terms">The employee confirms receipt of the listed company asset and accepts responsibility for its care, safe use, and return when requested or when employment/assignment conditions require it.</p>
            </article>

            <div class="signature-grid">
                <div class="signature-box"><div class="signature-line">Employee Signature</div></div>
                <div class="signature-box"><div class="signature-line">Authorized Signature</div></div>
            </div>
        </section>
    </main>
</body>
</html>
