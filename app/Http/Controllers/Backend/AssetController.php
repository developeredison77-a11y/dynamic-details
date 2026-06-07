<?php

namespace App\Http\Controllers\Backend;

use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetRequest;
use App\Models\Asset;
use App\Models\AssetBrand;
use App\Models\AssetCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $requestedPerPage = $request->integer('per_page', 10);
        $perPage = in_array($requestedPerPage, [10, 20, 30, 40, 50], true) ? $requestedPerPage : 10;

        return view('assets.index', [
            'assets' => Asset::query()
                ->with(['brand', 'category', 'activeAssignment.employee'])
                ->search($request->string('search')->toString())
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
                ->when($request->filled('category'), fn ($query) => $query->where('asset_category_id', $request->integer('category')))
                ->when($request->filled('brand'), fn ($query) => $query->where('asset_brand_id', $request->integer('brand')))
                ->latest()
                ->paginate($perPage)
                ->withQueryString(),
            'brands' => AssetBrand::query()->where('is_active', true)->orderBy('name')->get(),
            'categories' => AssetCategory::query()->orderBy('name')->get(),
            'statuses' => AssetStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('assets.form', $this->formData(new Asset()));
    }

    public function store(AssetRequest $request): RedirectResponse
    {
        Asset::query()->create($request->validated());

        return redirect()->route('assets.index')->with('success', 'Asset created successfully.');
    }

    public function edit(Asset $asset): View
    {
        return view('assets.form', $this->formData($asset));
    }

    public function update(AssetRequest $request, Asset $asset): RedirectResponse
    {
        $asset->update($request->validated());

        return redirect()->route('assets.index')->with('success', 'Asset updated successfully.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        if ($asset->assignments()->exists()) {
            return back()->with('warning', 'Assets with handover history cannot be deleted. Retire the asset instead.');
        }

        $asset->delete();

        return redirect()->route('assets.index')->with('success', 'Asset deleted successfully.');
    }

    private function formData(Asset $asset): array
    {
        return [
            'asset' => $asset,
            'brands' => AssetBrand::query()->where('is_active', true)->orderBy('name')->get(),
            'categories' => AssetCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'statuses' => AssetStatus::cases(),
            'conditions' => AssetCondition::cases(),
        ];
    }
}
