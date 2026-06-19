<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetBrand;
use App\Models\AssetCategory;
use App\Models\AssetReturn;
use App\Models\Employee;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $months = collect(range(5, 0))->map(fn (int $offset): Carbon => now()->subMonths($offset)->startOfMonth());
        $trendStart = $months->first();
        $trendEnd = $months->last()->copy()->endOfMonth();
        $monthExpression = match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%%Y-%%m', %s)",
            'pgsql' => "to_char(%s, 'YYYY-MM')",
            default => "DATE_FORMAT(%s, '%%Y-%%m')",
        };
        $statusColors = [
            AssetStatus::Available->value => '#22c55e',
            AssetStatus::Assigned->value => '#f59e0b',
            AssetStatus::Returned->value => '#38bdf8',
            AssetStatus::Maintenance->value => '#ef4444',
            AssetStatus::Retired->value => '#64748b',
        ];
        $assetStatusCounts = Asset::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $totalAssets = (int) $assetStatusCounts->sum();
        $assetStatusStats = collect(AssetStatus::cases())->map(function (AssetStatus $status) use ($assetStatusCounts, $statusColors, $totalAssets): array {
            $count = (int) ($assetStatusCounts[$status->value] ?? 0);

            return [
                'name' => $status->label(),
                'value' => $status->value,
                'count' => $count,
                'percentage' => $totalAssets > 0 ? round(($count / $totalAssets) * 100, 1) : 0,
                'color' => $statusColors[$status->value] ?? '#64748b',
            ];
        });
        $handoverMonthSql = sprintf($monthExpression, 'handover_date');
        $returnMonthSql = sprintf($monthExpression, 'returned_at');
        $handoverTrendCounts = AssetAssignment::query()
            ->whereBetween('handover_date', [$trendStart, $trendEnd])
            ->selectRaw($handoverMonthSql . ' as month, count(*) as aggregate')
            ->groupByRaw($handoverMonthSql)
            ->pluck('aggregate', 'month');
        $returnTrendCounts = AssetReturn::query()
            ->whereBetween('returned_at', [$trendStart, $trendEnd])
            ->selectRaw($returnMonthSql . ' as month, count(*) as aggregate')
            ->groupByRaw($returnMonthSql)
            ->pluck('aggregate', 'month');

        return view('dashboard.index', [
            'settings' => Setting::allSettings(),
            'assetStats' => [
                'total' => $totalAssets,
            ],
            'assetStatusStats' => $assetStatusStats,
            'employeeTotal' => Employee::query()->count(),
            'brandTotal' => AssetBrand::query()->count(),
            'categoryTotal' => AssetCategory::query()->count(),
            'handoverTrend' => $months->map(fn (Carbon $month): array => [
                'label' => $month->format('M'),
                'value' => (int) ($handoverTrendCounts[$month->format('Y-m')] ?? 0),
            ]),
            'returnTrend' => $months->map(fn (Carbon $month): array => [
                'label' => $month->format('M'),
                'value' => (int) ($returnTrendCounts[$month->format('Y-m')] ?? 0),
            ]),
        ]);
    }
}
