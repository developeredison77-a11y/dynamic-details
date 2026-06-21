<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Asset Return Form #{{ $return->id }}</title>
    <style>
        :root { color-scheme: light; }
        @page { size: A4; margin: 12mm; }
        * { box-sizing: border-box; }
        html { background: #f3f6fb; }
        body { margin: 0; background: #f3f6fb; color: #111827; font-family: Arial, sans-serif; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .print-actions { display: flex; justify-content: flex-end; max-width: 920px; margin: 24px auto 12px; }
        .print-actions button { min-height: 40px; border: 1px solid #1d4ed8; border-radius: 8px; background: #2563eb; color: #fff; cursor: pointer; font-weight: 700; padding: 0 18px; }
        .document { max-width: 920px; margin: 0 auto 36px; border: 1px solid #d8e0ec; border-radius: 10px; background: #fff; overflow: hidden; box-shadow: 0 18px 42px rgba(15, 23, 42, .10); }
        .document-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; border-bottom: 3px solid #1f3b66; padding: 28px 32px 24px; }
        .eyebrow { color: #64748b; font-size: 12px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        h1 { margin: 6px 0 0; color: #0f172a; font-size: 28px; line-height: 1.15; }
        .document-number { min-width: 170px; border: 1px solid #d8e0ec; border-radius: 8px; background: #f8fafc; padding: 14px; text-align: right; }
        .document-number strong { display: block; margin-top: 5px; font-size: 20px; }
        .summary-strip { display: grid; grid-template-columns: repeat(3, 1fr); border-bottom: 1px solid #d8e0ec; background: #f8fafc; }
        .summary-strip div { border-right: 1px solid #d8e0ec; padding: 16px 22px; }
        .summary-strip div:last-child { border-right: 0; }
        .summary-strip span, .section-title, dt { color: #64748b; font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
        .summary-strip strong { display: block; margin-top: 5px; color: #0f172a; font-size: 16px; }
        .content { display: grid; gap: 18px; padding: 24px 32px 32px; }
        .two-column { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .section { border: 1px solid #d8e0ec; border-radius: 8px; padding: 18px; }
        .section h2 { margin: 6px 0 16px; color: #0f172a; font-size: 18px; }
        dl { display: grid; gap: 10px; margin: 0; }
        dl div { display: flex; justify-content: space-between; gap: 16px; border-bottom: 1px solid #eef2f7; padding-bottom: 8px; }
        dl div:last-child { border-bottom: 0; padding-bottom: 0; }
        dd { margin: 0; color: #111827; font-weight: 700; text-align: right; }
        .notes, .terms { color: #334155; line-height: 1.7; margin: 8px 0 0; min-height: 70px; }
        .condition-badge { display: inline-flex; align-items: center; min-height: 30px; border: 1px solid #bfdbfe; border-radius: 999px; background: #eff6ff; color: #1d4ed8; font-weight: 800; padding: 0 12px; }
        .signature-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 42px; margin-top: 28px; }
        .signature-box { padding-top: 56px; }
        .signature-line { border-top: 1px solid #111827; padding-top: 10px; color: #334155; font-weight: 700; }
        .footer-note { border-top: 1px solid #d8e0ec; color: #64748b; font-size: 11px; line-height: 1.5; margin-top: 4px; padding-top: 12px; }
        @media print {
            html, body { width: 100%; min-height: 100%; background: #fff; }
            .print-actions { display: none; }
            .document { margin: 0; max-width: none; border: 0; border-radius: 0; box-shadow: none; overflow: visible; }
            .document-header { padding: 0 0 18px; }
            h1 { max-width: 460px; font-size: 24px; }
            .document-number { min-width: 145px; padding: 12px; }
            .summary-strip { border: 1px solid #d8e0ec; border-left: 0; border-right: 0; }
            .summary-strip div { padding: 13px 18px; }
            .content { display: block; padding: 18px 0 0; }
            .two-column { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
            .section { break-inside: avoid; margin-bottom: 14px; padding: 15px; }
            .section h2 { font-size: 17px; margin-bottom: 12px; }
            dl { gap: 8px; }
            dl div { padding-bottom: 7px; }
            .notes, .terms { min-height: 0; line-height: 1.55; }
            .signature-grid { break-inside: avoid; gap: 34px; margin-top: 18px; }
            .signature-box { padding-top: 48px; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="print-actions"><button type="button" onclick="window.print()">Print</button></div>
    <main class="document">
        <header class="document-header">
            <div>
                <span class="eyebrow">Asset Return</span>
                <h1>{{ $return->asset?->asset_tag }} returned by {{ $return->employee?->name_en }}</h1>
            </div>
            <div class="document-number">
                <span class="eyebrow">Reference</span>
                <strong>#{{ $return->id }}</strong>
            </div>
        </header>

        <section class="summary-strip">
            <div><span>Returned At</span><strong>{{ $return->returned_at?->format('M d, Y') }}</strong></div>
            <div><span>Condition</span><strong>{{ $return->condition?->label() }}</strong></div>
            <div><span>Asset Tag</span><strong>{{ $return->asset?->asset_tag }}</strong></div>
        </section>

        <section class="content">
            <div class="two-column">
                <article class="section">
                    <span class="section-title">Employee</span>
                    <h2>{{ $return->employee?->name_en }}</h2>
                    <dl>
                        <div><dt>Code</dt><dd>{{ $return->employee?->employee_code }}</dd></div>
                    </dl>
                </article>

                <article class="section">
                    <span class="section-title">Asset</span>
                    <h2>{{ $return->asset?->name }}</h2>
                    <dl>
                        <div><dt>Tag</dt><dd>{{ $return->asset?->asset_tag }}</dd></div>
                        <div><dt>Brand</dt><dd>{{ $return->asset?->brand?->name ?? '-' }}</dd></div>
                        <div><dt>Category</dt><dd>{{ $return->asset?->category?->name ?? '-' }}</dd></div>
                        <div><dt>Serial</dt><dd>{{ $return->asset?->serial_number ?: '-' }}</dd></div>
                    </dl>
                </article>
            </div>

            <article class="section">
                <span class="section-title">Return Condition</span>
                <h2><span class="condition-badge">{{ $return->condition?->label() }}</span></h2>
                <p class="notes">{{ $return->notes ?: 'No return notes recorded.' }}</p>
            </article>

            <article class="section">
                <span class="section-title">Return Confirmation</span>
                <p class="terms">The listed asset has been received from the employee on the return date above. The receiving team confirms the recorded condition and notes for asset custody and future lifecycle processing.</p>
            </article>

            <div class="signature-grid">
                <div class="signature-box"><div class="signature-line">Employee Signature</div></div>
                <div class="signature-box"><div class="signature-line">Receiver Signature</div></div>
            </div>

            <p class="footer-note">This document records asset return custody, condition, and receiver acknowledgement for internal asset management records.</p>
        </section>
    </main>
</body>
</html>
