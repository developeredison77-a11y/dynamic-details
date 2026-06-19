<?php

namespace App\Http\Controllers\Backend;

use App\Enums\AssetCondition;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetReturnRequest;
use App\Models\AssetAssignment;
use App\Models\AssetReturn;
use App\Services\AssetLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AssetReturnController extends Controller
{
    public function index(): View
    {
        return view('asset-returns.index', [
            'assignments' => AssetAssignment::query()
                ->assigned()
                ->with([
                    'employee:id,employee_code,name_en',
                    'asset:id,asset_tag,name',
                ])
                ->latest()
                ->get(['id', 'employee_id', 'asset_id', 'status', 'created_at']),
            'returns' => AssetReturn::query()
                ->with([
                    'employee:id,name_en',
                    'asset:id,asset_tag',
                ])
                ->latest()
                ->paginate(12),
            'conditions' => AssetCondition::cases(),
        ]);
    }

    public function store(AssetReturnRequest $request, AssetAssignment $assignment, AssetLifecycleService $service): RedirectResponse
    {
        try {
            $return = $service->returnAsset($assignment, $request->validated(), $request->user()?->id);
        } catch (\DomainException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('asset-returns.print', $return)->with('success', 'Asset return saved successfully.');
    }

    public function print(AssetReturn $assetReturn): View
    {
        return view('asset-returns.print', ['return' => $assetReturn->load([
            'employee:id,employee_code,name_en',
            'asset:id,asset_brand_id,asset_category_id,asset_tag,name,serial_number',
            'asset.brand:id,name',
            'asset.category:id,name',
        ])]);
    }
}
