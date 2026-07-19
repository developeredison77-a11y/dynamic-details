@php
    $assignment = $declaration->assignment;
    $employee = $assignment?->employee;
    $asset = $assignment?->asset;
    $autoPrint = $autoPrint ?? false;
    $showToolbar = $showToolbar ?? false;
    $companyName = $appSettings['site_name'] ?? config('app.name', 'AL Saud Investment');
    $formDate = $declaration->issued_at ?? $assignment?->handover_date;
    $handoverDate = $assignment?->handover_date;
    $dateFormat = 'd/m/Y';
    $equipmentRows = collect();

    if ($asset) {
        $equipmentRows->push([
            'asset_tag' => $asset->asset_tag ?: '-',
            'name' => $asset->name ?: ($asset->category?->name ?? '-'),
            'category' => $asset->category?->name ?? ($asset->brand?->name ?? '-'),
            'model' => $asset->model ?: '-',
            'serial' => $asset->serial_number ?: '-',
            'checkout_date' => $handoverDate?->format($dateFormat) ?? '-',
        ]);
    }

    $equipmentRowCount = max(4, $equipmentRows->count());
    $additionalInfo = trim((string) ($assignment?->handover_notes ?? ''));
    $acknowledgement = trim((string) ($declaration->terms ?? ''));
@endphp

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Asset Hand Over {{ $declaration->declaration_number }}</title>
    <style>
        :root {
            color-scheme: light;
            --form-blue: #265f99;
            --form-grey: #a6a6a6;
            --ink: #000;
            --logo-ink: #172642;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
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

        .document-btn,
        .document-upload {
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

        .document-upload {
            position: relative;
        }

        .document-upload input {
            height: 1px;
            inset: auto;
            opacity: 0;
            overflow: hidden;
            position: absolute;
            width: 1px;
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

        .top-spacer {
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
            font-size: 7pt;
            font-weight: 800;
            height: 12pt;
            line-height: 1;
            text-align: center;
        }

        .equipment-row td {
            font-size: 7.4pt;
            height: 27pt;
            line-height: 1.22;
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

        .additional-bar {
            height: 30pt;
        }

        .additional-row td {
            font-size: 6.8pt;
            font-weight: 800;
            height: 26pt;
            line-height: 1.15;
            padding: 0 2pt !important;
            text-align: left;
        }

        .lower-spacer {
            height: 12pt;
        }

        .acknowledgement-cell {
            font-size: 7.2pt;
            font-style: italic;
            font-weight: 800;
            height: 26pt;
            line-height: 1.35;
            padding: 2pt !important;
            text-align: left;
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
                <strong>{{ $declaration->declaration_number }}</strong>
                <span>{{ $employee?->name_en ?? 'Employee' }} / {{ $asset?->asset_tag ?? 'Asset' }}</span>
            </div>

            <div class="document-toolbar-actions">
                <a class="document-btn" href="{{ route('declarations.index') }}">Back</a>
                @if ($assignment)
                    <a class="document-btn" href="{{ route('asset-handovers.show', $assignment) }}">Handover</a>
                @endif
                @if ($declaration->signed_file_path)
                    <a class="document-btn" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($declaration->signed_file_path) }}" target="_blank">Signed Copy</a>
                @endif
                @if (auth()->user()?->canAccess('declarations.create'))
                    <form method="POST" action="{{ route('declarations.signed', $declaration) }}" enctype="multipart/form-data">
                        @csrf
                        <label class="document-upload">
                            <input type="file" name="signed_file" accept=".pdf,.jpg,.jpeg,.png,.webp" required onchange="this.form.submit()">
                            <span>{{ $declaration->signed_file_path ? 'Replace Signed Copy' : 'Upload Signed Copy' }}</span>
                        </label>
                    </form>
                @endif
                <button class="document-btn document-btn-primary" type="button" onclick="window.print()">Print</button>
            </div>
        </div>
    @endif

    <div class="document-shell">
        <main class="asset-sheet">
            <section class="asset-handover-form" aria-label="Asset hand over form">
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
                            <th class="title-cell" colspan="5">ASSET HAND OVER FORM</th>
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
                            <td class="info-value" colspan="3">{{ $employee?->department ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label" colspan="2">Employee Name:</td>
                            <td class="info-value" colspan="3">{{ $employee?->name_en ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label" colspan="2">EID:</td>
                            <td class="info-value" colspan="3">{{ $employee?->employee_code ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label" colspan="2">Date:</td>
                            <td class="info-value" colspan="3">{{ $formDate?->format($dateFormat) ?? '-' }}</td>
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
                            <th>Model</th>
                            <th>Serial</th>
                            <th>Check Out Date</th>
                            <th>Signature</th>
                        </tr>
                        @for ($rowIndex = 0; $rowIndex < $equipmentRowCount; $rowIndex++)
                            @php($row = $equipmentRows->get($rowIndex))
                            <tr class="equipment-row">
                                <td class="sl-cell">{{ $row ? $rowIndex + 1 : '' }}</td>
                                <td><span class="cell-fit">{{ $row['asset_tag'] ?? '' }}</span></td>
                                <td><span class="cell-fit">{{ $row['name'] ?? '' }}</span></td>
                                <td><span class="cell-fit">{{ $row['category'] ?? '' }}</span></td>
                                <td><span class="cell-fit">{{ $row['model'] ?? '' }}</span></td>
                                <td><span class="cell-fit">{{ $row['serial'] ?? '' }}</span></td>
                                <td><span class="cell-fit">{{ $row['checkout_date'] ?? '' }}</span></td>
                                <td></td>
                            </tr>
                        @endfor
                        <tr>
                            <th class="section-bar-large" colspan="8">Software and user credentials</th>
                        </tr>
                        <tr>
                            <td class="credentials-space"></td>
                            <td class="credentials-space" colspan="7"></td>
                        </tr>
                        <tr>
                            <th class="section-bar-large additional-bar" colspan="8">Adiitional Info Handed Over</th>
                        </tr>
                        <tr class="additional-row">
                            <td></td>
                            <td colspan="7">
                                {{ $additionalInfo !== '' ? $additionalInfo : 'Im pleased to confirm that all data has been successfully transferred and there are no missing files. Our team has ensured data integrity throughout the process.' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="lower-spacer" colspan="8"></td>
                        </tr>
                        <tr>
                            <td class="acknowledgement-cell" colspan="8">
                                {{ $acknowledgement !== '' ? $acknowledgement : 'By initialing above and signing below, I acknowledge that the items handed over are my responsibility until I return them. I understand that if they are stolen, lost, or damaged while in my care, I will be responsible for replacing them at my own cost.' }}
                            </td>
                        </tr>
                        <tr class="signature-row">
                            <td></td>
                            <td class="signature-label">Handed over to User :</td>
                            <td colspan="3"></td>
                            <td colspan="2"></td>
                            <td></td>
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
                            <td class="signature-label">Handed over by IT :</td>
                            <td colspan="3"></td>
                            <td colspan="2"></td>
                            <td></td>
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
