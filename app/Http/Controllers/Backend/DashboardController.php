<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Enums\AssetStatus;
use App\Enums\EmployeeStatus;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetReturn;
use App\Models\Employee;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $months = collect(range(5, 0))->map(fn (int $offset): Carbon => now()->subMonths($offset)->startOfMonth());

        return view('dashboard.index', [
            'settings' => Setting::allSettings(),
            'assetStats' => [
                'total' => Asset::query()->count(),
                'assigned' => Asset::query()->where('status', AssetStatus::Assigned)->count(),
                'available' => Asset::query()->where('status', AssetStatus::Available)->count(),
                'returned' => Asset::query()->where('status', AssetStatus::Returned)->count(),
            ],
            'employeeStats' => [
                'active' => Employee::query()->where('status', EmployeeStatus::Active)->count(),
                'leave' => Employee::query()->where('status', EmployeeStatus::Leave)->count(),
                'resigned' => Employee::query()->where('status', EmployeeStatus::Resigned)->count(),
            ],
            'categoryStats' => Asset::query()
                ->join('asset_categories', 'assets.asset_category_id', '=', 'asset_categories.id')
                ->selectRaw('asset_categories.name as label, count(*) as value')
                ->groupBy('asset_categories.name')
                ->orderByDesc('value')
                ->get(),
            'brandStats' => Asset::query()
                ->leftJoin('asset_brands', 'assets.asset_brand_id', '=', 'asset_brands.id')
                ->selectRaw("coalesce(asset_brands.name, 'Unbranded') as label, count(*) as value")
                ->groupBy('asset_brands.name')
                ->orderByDesc('value')
                ->get(),
            'handoverTrend' => $months->map(fn (Carbon $month): array => [
                'label' => $month->format('M'),
                'value' => AssetAssignment::query()->whereBetween('handover_date', [$month, $month->copy()->endOfMonth()])->count(),
            ]),
            'returnTrend' => $months->map(fn (Carbon $month): array => [
                'label' => $month->format('M'),
                'value' => AssetReturn::query()->whereBetween('returned_at', [$month, $month->copy()->endOfMonth()])->count(),
            ]),
            'recentHandovers' => AssetAssignment::query()->with(['employee', 'asset'])->latest()->limit(5)->get(),
        ]);
    }
}
