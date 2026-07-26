@php
    $autoPrint = $autoPrint ?? false;
    $showToolbar = $showToolbar ?? false;
    $companyName = $appSettings['site_name'] ?? config('app.name', 'AL Saud Investment');
    $employeeName = $employee->name_en ?: '______________________________';
    $employeeArabicName = $employee->name_ar ?: $employeeName;
    $designation = $employee->role?->name ?? $employee->designation ?? '______________________________';
    $eid = $employee->eid ?: '______________________________';
    $declarationDate = $assetSummary['date'] ?? '____/____/______';
    $assetEnglish = $assetSummary['english'] ?? 'the following company asset(s): ______________________________';
    $assetArabic = $assetSummary['arabic'] ?? 'أصول الشركة التالية: ______________________________';

    $englishParagraphs = [
        "I, {$employeeName} the undersigned, holder of Emirates ID No ({$eid}) in my capacity as a staff member of {$companyName}, with job title {$designation}, I hereby acknowledge that I had received from the company, {$assetEnglish} at date {$declarationDate}",
        'for the purpose of carrying out the tasks that I was assigned, I undertake to preserve it and acknowledge that it is in my possession and my full civil and criminal responsibility from the above-mentioned date.',
        'I undertake to not to use it in a way that offends the company and its reputation, and hand it over to the company whenever I am requested to do so, in accordance with the rules in force within the company',
    ];

    $arabicParagraphs = [
        "أنا الموقع أدناه/ {$employeeArabicName} حامل بطاقة هوية رقم ({$eid}) بصفتي أحد العاملين بشركة {$companyName} بموجب وظيفة {$designation}، أقر بأنني استلمت من الشركة {$assetArabic} وذلك بتاريخ {$declarationDate}",
        'وذلك بغرض تسيير قيامي بمهام عملي، وأتعهد بالمحافظة عليه، وأقر بوجوده بحوزتي ومسؤوليتي المدنية والجنائية الكاملة عنها من التاريخ سالف الذكر.',
        'وأتعهد بعدم استخدامه بشكل يسيء إلى الشركة وسمعتها، وتسليمه للشركة متى طلب مني ذلك، وفقاً للقواعد المعمول بها داخل الشركة.',
    ];
@endphp

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Declaration Form - {{ $employee->name_en }}</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #000;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef2f7;
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
        }

        .document-toolbar {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: space-between;
            margin: 20px auto;
            max-width: 920px;
            padding: 0 16px;
        }

        .document-toolbar-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .document-toolbar-title {
            color: #111827;
            display: grid;
            gap: 2px;
            font-size: 13px;
        }

        .document-toolbar-title strong {
            font-size: 16px;
        }

        .document-btn {
            align-items: center;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            color: #111827;
            cursor: pointer;
            display: inline-flex;
            font: 700 13px/1 Arial, Helvetica, sans-serif;
            min-height: 38px;
            padding: 0 14px;
            text-decoration: none;
        }

        .document-btn-primary {
            background: #2563eb;
            border-color: #1d4ed8;
            color: #fff;
        }

        .document-shell {
            overflow-x: auto;
            padding: 0 16px 36px;
        }

        .declaration-page {
            background: #fff;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .16);
            height: 792pt;
            margin: 0 auto;
            position: relative;
            width: 612pt;
        }

        .declaration-title {
            left: 0;
            position: absolute;
            right: 0;
            text-align: center;
            top: 103pt;
        }

        .declaration-title .arabic-title {
            direction: rtl;
            font-size: 14.5pt;
            font-weight: 800;
            line-height: 1;
            margin: 0 0 7pt;
        }

        .declaration-title .english-title {
            font-size: 10.2pt;
            font-weight: 800;
            line-height: 1;
            margin: 0;
        }

        .declaration-table {
            border: 1.5pt solid var(--ink);
            border-collapse: collapse;
            height: 548pt;
            left: 58pt;
            position: absolute;
            table-layout: fixed;
            top: 167pt;
            width: 495pt;
        }

        .declaration-table td {
            border: 1.2pt solid var(--ink);
            padding: 0;
            vertical-align: top;
            width: 50%;
        }

        .body-row {
            height: 430pt;
        }

        .declaration-copy {
            display: flex;
            flex-direction: column;
            font-size: 12.4pt;
            font-weight: 500;
            height: 100%;
            justify-content: space-between;
            line-height: 1.25;
            overflow: hidden;
            padding: 17pt 2.5pt 11pt;
        }

        .declaration-copy p {
            margin: 0;
        }

        .declaration-copy-en {
            text-align: left;
        }

        .declaration-copy-ar {
            direction: rtl;
            font-size: 12.2pt;
            line-height: 1.32;
            text-align: right;
        }

        .signature-row {
            height: 118pt;
        }

        .signature-cell {
            font-size: 10.2pt;
            font-weight: 800;
            line-height: 1.1;
            padding: 46pt 2pt 0 !important;
        }

        .signature-cell-ar {
            direction: rtl;
            text-align: right;
        }

        .signature-cell strong {
            display: block;
            margin-bottom: 14pt;
        }

        @media print {
            @page {
                margin: 0;
                size: letter;
            }

            body {
                background: #fff;
            }

            .no-print {
                display: none !important;
            }

            .document-shell {
                overflow: visible;
                padding: 0;
            }

            .declaration-page {
                box-shadow: none;
                margin: 0;
            }
        }
    </style>
</head>
<body @if($autoPrint) onload="window.print()" @endif>
    @if ($showToolbar)
        <div class="document-toolbar no-print">
            <div class="document-toolbar-title">
                <strong>Declaration Form</strong>
                <span>{{ $employee->name_en }} / {{ $employee->employee_code }}</span>
            </div>
            <div class="document-toolbar-actions">
                <a class="document-btn" href="{{ route('employees.index') }}">Back</a>
                <a class="document-btn" href="{{ route('employees.declaration-form.print', $employee) }}" target="_blank">Print View</a>
                <button class="document-btn document-btn-primary" type="button" onclick="window.print()">Print</button>
            </div>
        </div>
    @endif

    <div class="document-shell">
        <main class="declaration-page" aria-label="Declaration and undertaking form">
            <header class="declaration-title">
                <h1 class="arabic-title">إقرار وتعهد</h1>
                <h2 class="english-title">Declaration and Undertaking</h2>
            </header>

            <table class="declaration-table">
                <tbody>
                    <tr class="body-row">
                        <td>
                            <div class="declaration-copy declaration-copy-en">
                                @foreach ($englishParagraphs as $paragraph)
                                    <p>{{ $paragraph }}</p>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <div class="declaration-copy declaration-copy-ar" dir="rtl">
                                @foreach ($arabicParagraphs as $paragraph)
                                    <p>{{ $paragraph }}</p>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    <tr class="signature-row">
                        <td class="signature-cell">
                            <strong>Signature:</strong>
                            <strong>Name:</strong>
                        </td>
                        <td class="signature-cell signature-cell-ar" dir="rtl">
                            <strong>التوقيع:</strong>
                            <strong>الاسم:</strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
