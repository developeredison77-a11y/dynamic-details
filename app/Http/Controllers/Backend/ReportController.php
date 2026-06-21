<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetDeclaration;
use App\Models\AssetReturn;
use App\Models\Employee;
use App\Services\ReportExportService;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index', $this->reportData());
    }

    public function export(string $type, ReportExportService $export)
    {
        abort_unless(in_array($type, array_keys($this->reportTitles()), true) && $type !== 'overview', 404);

        $rows = $this->rowsFor($type);

        return $export->csv("{$type}-report.csv", $rows);
    }

    public function pdf(string $type): View
    {
        abort_unless(in_array($type, array_keys($this->reportTitles()), true), 404);

        return view('reports.print', [
            ...$this->reportData(),
            'type' => $type,
            'title' => $this->reportTitles()[$type],
            'rows' => $type === 'overview' ? [] : $this->rowsFor($type),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function reportData(): array
    {
        $assetsByStatus = Asset::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $employeesByStatus = Employee::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'summary' => [
                'assets' => Asset::query()->count(),
                'employees' => Employee::query()->count(),
                'activeHandovers' => AssetAssignment::query()->assigned()->count(),
                'returns' => AssetReturn::query()->count(),
                'declarations' => AssetDeclaration::query()->count(),
                'signedReturns' => AssetReturn::query()->whereNotNull('signed_file_path')->count(),
            ],
            'assetsByStatus' => $assetsByStatus,
            'employeesByStatus' => $employeesByStatus,
            'assets' => Asset::query()->with(['brand', 'category', 'activeAssignment.employee'])->latest()->limit(12)->get(),
            'employees' => Employee::query()->with('role')->latest()->limit(12)->get(),
            'handovers' => AssetAssignment::query()->with(['employee', 'asset'])->latest()->limit(12)->get(),
            'returns' => AssetReturn::query()->with(['employee', 'asset'])->latest()->limit(12)->get(),
            'declarations' => AssetDeclaration::query()->with(['assignment.employee', 'assignment.asset'])->latest()->limit(12)->get(),
            'reportTypes' => $this->reportTitles(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function reportTitles(): array
    {
        return [
            'overview' => 'Executive Overview',
            'assets' => 'Asset Inventory',
            'employees' => 'Employee Master',
            'handovers' => 'Asset Handovers',
            'returns' => 'Asset Returns',
            'declarations' => 'Declaration Forms',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rowsFor(string $type): array
    {
        return match ($type) {
            'employees' => Employee::query()->with('role')->get()->map(fn ($employee): array => [
                'Code' => $employee->employee_code,
                'Name English' => $employee->name_en,
                'Name Arabic' => $employee->name_ar,
                'Department' => $employee->department,
                'Role' => $employee->role?->name ?? $employee->designation,
                'Status' => $employee->status?->label(),
            ])->all(),
            'handovers' => AssetAssignment::query()->with(['employee', 'asset'])->get()->map(fn ($assignment): array => [
                'Employee' => $assignment->employee?->name_en,
                'Asset' => $assignment->asset?->asset_tag,
                'Status' => $assignment->status?->label(),
                'Handover Date' => $assignment->handover_date?->format('Y-m-d'),
                'Returned At' => $assignment->returned_at?->format('Y-m-d'),
            ])->all(),
            'returns' => AssetReturn::query()->with(['employee', 'asset'])->get()->map(fn ($return): array => [
                'Employee' => $return->employee?->name_en,
                'Employee Code' => $return->employee?->employee_code,
                'Asset' => $return->asset?->asset_tag,
                'Returned At' => $return->returned_at?->format('Y-m-d'),
                'Condition' => $return->condition?->label(),
                'Signed Copy' => $return->signed_file_path ? 'Uploaded' : 'Pending',
            ])->all(),
            'declarations' => AssetDeclaration::query()->with(['assignment.employee', 'assignment.asset'])->get()->map(fn ($declaration): array => [
                'Declaration No' => $declaration->declaration_number,
                'Employee' => $declaration->assignment?->employee?->name_en,
                'Asset' => $declaration->assignment?->asset?->asset_tag,
                'Issued At' => $declaration->issued_at?->format('Y-m-d'),
                'Signed Copy' => $declaration->signed_file_path ? 'Uploaded' : 'Pending',
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
    }
}
