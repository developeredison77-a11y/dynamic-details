<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetBrand;
use App\Models\AssetCategory;
use App\Models\Employee;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $categoryColors = [
            '#f5a800',
            '#0f5bd8',
            '#06870a',
            '#d80000',
            '#7b10bf',
            '#22c55e',
            '#38bdf8',
            '#64748b',
            '#db2777',
            '#14b8a6',
            '#ea580c',
            '#4f46e5',
            '#84cc16',
            '#0891b2',
            '#be123c',
            '#9333ea',
        ];
        $categoryColor = static function (int $index) use ($categoryColors): string {
            if (isset($categoryColors[$index])) {
                return $categoryColors[$index];
            }

            $hue = fmod(37 + ($index * 137.508), 360);

            return 'hsl(' . round($hue, 3) . ' 78% 44%)';
        };
        $totalAssets = Asset::query()->count();
        $assetLastUpdate = Asset::query()->max('updated_at');
        $assetCategoryStats = AssetCategory::query()
            ->leftJoin('assets', 'assets.asset_category_id', '=', 'asset_categories.id')
            ->select('asset_categories.id', 'asset_categories.name', DB::raw('COUNT(assets.id) as asset_count'))
            ->groupBy('asset_categories.id', 'asset_categories.name')
            ->orderByDesc('asset_count')
            ->orderBy('asset_categories.name')
            ->get()
            ->filter(fn (AssetCategory $category): bool => (int) $category->asset_count > 0)
            ->values()
            ->map(fn (AssetCategory $category, int $index): array => [
                'name' => $category->name,
                'value' => (string) $category->id,
                'count' => (int) $category->asset_count,
                'percentage' => $totalAssets > 0 ? round(((int) $category->asset_count / $totalAssets) * 100, 1) : 0,
                'color' => $categoryColor($index),
            ]);
        $hardwareDistribution = $assetCategoryStats
            ->map(fn (array $category): array => [
                'label' => $category['name'],
                'value' => $category['count'],
                'color' => $category['color'],
            ]);

        return view('dashboard.index', [
            'settings' => Setting::allSettings(),
            'assetStats' => [
                'total' => $totalAssets,
            ],
            'assetStatusStats' => $assetCategoryStats,
            'assetStatusSummary' => [
                'categoryCount' => $assetCategoryStats->count(),
                'topCategory' => $assetCategoryStats->first()['name'] ?? '-',
                'lastUpdate' => $assetLastUpdate ? Carbon::parse($assetLastUpdate) : null,
            ],
            'employeeTotal' => Employee::query()->count(),
            'brandTotal' => AssetBrand::query()->count(),
            'categoryTotal' => AssetCategory::query()->count(),
            'hardwareDistribution' => $hardwareDistribution,
        ]);
    }
}
