<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetReturn;
use App\Services\ReportExportService;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index', [
            'assets' => Asset::query()->with(['brand', 'category', 'activeAssignment.employee'])->latest()->get(),
            'handovers' => AssetAssignment::query()->with(['employee', 'asset'])->latest()->limit(20)->get(),
            'returns' => AssetReturn::query()->with(['employee', 'asset'])->latest()->limit(20)->get(),
        ]);
    }

    public function export(string $type, ReportExportService $export)
    {
        $rows = match ($type) {
            'employees' => \App\Models\Employee::query()->get()->map(fn ($employee): array => [
                'Code' => $employee->employee_code,
                'Name English' => $employee->name_en,
                'Name Arabic' => $employee->name_ar,
                'Department' => $employee->department,
                'Status' => $employee->status?->label(),
            ])->all(),
            'handovers' => AssetAssignment::query()->with(['employee', 'asset'])->get()->map(fn ($assignment): array => [
                'Employee' => $assignment->employee?->name_en,
                'Asset' => $assignment->asset?->asset_tag,
                'Status' => $assignment->status?->label(),
                'Handover Date' => $assignment->handover_date?->format('Y-m-d'),
                'Returned At' => $assignment->returned_at?->format('Y-m-d'),
            ])->all(),
            default => Asset::query()->with(['brand', 'category'])->get()->map(fn ($asset): array => [
                'Asset Tag' => $asset->asset_tag,
                'Name' => $asset->name,
                'Category' => $asset->category?->name,
                'Brand' => $asset->brand?->name,
                'Serial' => $asset->serial_number,
                'Status' => $asset->status?->label(),
            ])->all(),
        };

        return $export->csv("{$type}-report.csv", $rows);
    }
}
