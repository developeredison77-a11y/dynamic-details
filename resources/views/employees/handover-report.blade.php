@php
    $autoPrint = $autoPrint ?? false;
    $showToolbar = $showToolbar ?? false;
    $companyName = $appSettings['site_name'] ?? config('app.name', 'AL Saud Investment');
    $dateFormat = 'd/m/Y';
    $generatedAt = now(config('app.timezone', 'Asia/Kolkata'));
    $equipmentRows = $assignments->map(function ($assignment) use ($dateFormat): array {
        $asset = $assignment->asset;
        $returnDate = $assignment->returned_at ?? $assignment->returnRecord?->returned_at;
        $status = $assignment->status?->label() ?? '-';

        if ($returnDate) {
            $status .= ' '.$returnDate->format($dateFormat);
        }

        return [
            'asset_tag' => $asset?->asset_tag ?: '-',
            'name' => $asset?->name ?: ($asset?->category?->name ?? '-'),
            'category' => $asset?->category?->name ?? '-',
            'brand_model' => trim(collect([$asset?->brand?->name, $asset?->model])->filter()->implode(' / ')) ?: '-',
            'serial_condition' => trim(collect([$asset?->serial_number, $asset?->condition?->label()])->filter()->implode(' / ')) ?: '-',
            'checkout_date' => $assignment->handover_date?->format($dateFormat) ?? '-',
            'status' => $status,
        ];
    });
    $equipmentRowCount = max(4, $equipmentRows->count());
    $activeCount = $assignments->filter(fn ($assignment) => $assignment->status?->value === 'assigned')->count();
    $returnedCount = $assignments->filter(fn ($assignment) => $assignment->status?->value === 'returned')->count();
@endphp

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Employee Handover Report - {{ $employee->name_en }}</title>
    <style>
        :root {
            color-scheme: light;
            --form-blue: #265f99;
            --form-grey: #a6a6a6;
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
            gap: 8px;
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

        .asset-sheet {
            background: #fff;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .16);
            margin: 0 auto;
            min-height: 11in;
            padding: .805in .5in 0;
            width: 8.5in;
        }

        .asset-handover-form {
            width: 540pt;
        }

        .asset-handover-form table {
            border-collapse: collapse;
            table-layout: fixed;
            width: 100%;
        }

        .asset-handover-form th,
        .asset-handover-form td {
            border: 1pt solid var(--ink);
            color: var(--ink);
            padding: 0;
            vertical-align: middle;
        }

        .title-cell {
            background: var(--form-blue);
            color: #fff !important;
            font-size: 8pt;
            font-weight: 800;
            height: 42pt;
            text-align: center;
            text-decoration: underline;
        }

        .logo-cell {
            background: #fff;
            height: 149.5pt;
            text-align: center;
        }

        .logo-cell img {
            display: block;
            height: 92pt;
            margin: 0 auto;
            object-fit: contain;
            width: auto;
        }

        .info-label,
        .info-value {
            font-size: 7.4pt;
            height: 21.5pt;
            line-height: 1.15;
        }

        .info-label {
            font-weight: 800;
            padding-left: 2pt !important;
            text-align: left;
        }

        .info-value {
            font-weight: 800;
            padding: 0 4pt !important;
            text-align: center;
        }

        .top-spacer,
        .lower-spacer {
            height: 12pt;
        }

        .section-bar,
        .section-bar-large {
            background: var(--form-blue);
            color: #fff !important;
            font-weight: 800;
            text-align: center;
        }

        .section-bar {
            font-size: 8pt;
            height: 13pt;
        }

        .section-bar-large {
            font-size: 10pt;
            height: 27pt;
        }

        .equipment-head th {
            background: var(--form-grey);
            font-size: 6.7pt;
            font-weight: 800;
            height: 12pt;
            line-height: 1;
            text-align: center;
        }

        .equipment-row td {
            font-size: 7pt;
            height: 27pt;
            line-height: 1.18;
            padding: 0 2pt !important;
            text-align: center;
        }

        .equipment-row .sl-cell {
            font-weight: 800;
        }

        .equipment-row .cell-fit {
            display: block;
            max-height: 25pt;
            overflow: hidden;
            overflow-wrap: anywhere;
        }

        .credentials-space {
            height: 45pt;
        }

        .credentials-detail,
        .additional-row td,
        .acknowledgement-cell {
            font-size: 6.8pt;
            font-weight: 800;
            line-height: 1.2;
            padding: 2pt !important;
            text-align: left;
        }

        .additional-bar {
            height: 30pt;
        }

        .additional-row td {
            height: 35pt;
        }

        .acknowledgement-cell {
            font-style: italic;
            height: 28pt;
            line-height: 1.35;
        }

        .signature-row td {
            font-size: 7.4pt;
            height: 15.5pt;
            line-height: 1.1;
            padding: 0 2pt !important;
            text-align: left;
        }

        .signature-label {
            white-space: nowrap;
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

            .asset-sheet {
                box-shadow: none;
                margin: 0;
                min-height: 11in;
                padding: .805in .5in 0;
                width: 8.5in;
            }
        }
    </style>
</head>
<body @if($autoPrint) onload="window.print()" @endif>
    @if ($showToolbar)
        <div class="document-toolbar no-print">
            <div class="document-toolbar-title">
                <strong>Employee Handover Report</strong>
                <span>{{ $employee->name_en }} / {{ $employee->employee_code }}</span>
            </div>
            <div class="document-toolbar-actions">
                <a class="document-btn" href="{{ route('employees.index') }}">Back</a>
                <a class="document-btn" href="{{ route('employees.handover-report.print', $employee) }}" target="_blank">Print View</a>
                <button class="document-btn document-btn-primary" type="button" onclick="window.print()">Print</button>
            </div>
        </div>
    @endif

    <div class="document-shell">
        <main class="asset-sheet">
            <section class="asset-handover-form" aria-label="Employee asset handover report">
                <table>
                    <colgroup>
                        <col style="width: 30pt">
                        <col style="width: 85pt">
                        <col style="width: 67pt">
                        <col style="width: 68pt">
                        <col style="width: 75pt">
                        <col style="width: 89pt">
                        <col style="width: 56pt">
                        <col style="width: 70pt">
                    </colgroup>
                    <tbody>
                        <tr>
                            <th class="title-cell" colspan="5">EMPLOYEE ASSET HANDOVER REPORT</th>
                            <td class="logo-cell" colspan="3" rowspan="6">
                                <img src="{{ asset('images/asset-handover-logo.png') }}" alt="Company logo">
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label" colspan="2">Company Name:</td>
                            <td class="info-value" colspan="3">{{ $companyName }}</td>
                        </tr>
                        <tr>
                            <td class="info-label" colspan="2">Department:</td>
                            <td class="info-value" colspan="3">{{ $employee->employeeDepartment?->name ?? $employee->department ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label" colspan="2">Employee Name:</td>
                            <td class="info-value" colspan="3">{{ $employee->name_en ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label" colspan="2">Emirates ID:</td>
                            <td class="info-value" colspan="3">{{ $employee->eid ?: ($employee->employee_code ?: '-') }}</td>
                        </tr>
                        <tr>
                            <td class="info-label" colspan="2">Report Date:</td>
                            <td class="info-value" colspan="3">{{ $generatedAt->format($dateFormat) }}</td>
                        </tr>
                        <tr>
                            <td class="top-spacer" colspan="8"></td>
                        </tr>
                        <tr>
                            <th class="section-bar" colspan="8">Equipments</th>
                        </tr>
                        <tr class="equipment-head">
                            <th>Sl No</th>
                            <th>Asset Tag</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Brand / Model</th>
                            <th>Serial / Condition</th>
                            <th>Check Out Date</th>
                            <th>Status</th>
                        </tr>
                        @for ($rowIndex = 0; $rowIndex < $equipmentRowCount; $rowIndex++)
                            @php($row = $equipmentRows->get($rowIndex))
                            <tr class="equipment-row">
                                <td class="sl-cell">{{ $row ? $rowIndex + 1 : '' }}</td>
                                <td><span class="cell-fit">{{ $row['asset_tag'] ?? '' }}</span></td>
                                <td><span class="cell-fit">{{ $row['name'] ?? '' }}</span></td>
                                <td><span class="cell-fit">{{ $row['category'] ?? '' }}</span></td>
                                <td><span class="cell-fit">{{ $row['brand_model'] ?? '' }}</span></td>
                                <td><span class="cell-fit">{{ $row['serial_condition'] ?? '' }}</span></td>
                                <td><span class="cell-fit">{{ $row['checkout_date'] ?? '' }}</span></td>
                                <td><span class="cell-fit">{{ $row['status'] ?? '' }}</span></td>
                            </tr>
                        @endfor
                        <tr>
                            <th class="section-bar-large" colspan="8">Software and user credentials</th>
                        </tr>
                        <tr>
                            <td class="credentials-space"></td>
                            <td class="credentials-space credentials-detail" colspan="7"></td>
                        </tr>
                        <tr>
                            <th class="section-bar-large additional-bar" colspan="8">Additional Info Handed Over</th>
                        </tr>
                        <tr class="additional-row">
                            <td></td>
                            <td colspan="7">
                                @if ($assignments->isEmpty())
                                    No asset handovers or assignments are recorded for this employee.
                                @else
                                    This report includes only handover records linked to {{ $employee->name_en }}. Active assignments: {{ $activeCount }}. Returned assignments: {{ $returnedCount }}.
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="lower-spacer" colspan="8"></td>
                        </tr>
                        <tr>
                            <td class="acknowledgement-cell" colspan="8">
                                The listed assets are filtered by the selected employee record and exclude assets assigned or handed over to other employees.
                            </td>
                        </tr>
                        <tr class="signature-row">
                            <td></td>
                            <td class="signature-label">Employee :</td>
                            <td colspan="3">{{ $employee->name_en }}</td>
                            <td colspan="2"></td>
                            <td>{{ $generatedAt->format($dateFormat) }}</td>
                        </tr>
                        <tr class="signature-row">
                            <td></td>
                            <td></td>
                            <td colspan="3">(Name)</td>
                            <td colspan="2">Signature</td>
                            <td>Date</td>
                        </tr>
                        <tr class="signature-row">
                            <td></td>
                            <td class="signature-label">Verified by IT :</td>
                            <td colspan="3">{{ auth()->user()?->name ?? 'IT Department' }}</td>
                            <td colspan="2"></td>
                            <td>{{ $generatedAt->format($dateFormat) }}</td>
                        </tr>
                        <tr class="signature-row">
                            <td></td>
                            <td></td>
                            <td colspan="3">(Name)</td>
                            <td colspan="2">Signature</td>
                            <td>Date</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
