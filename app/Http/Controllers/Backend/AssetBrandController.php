<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetBrandRequest;
use App\Models\AssetBrand;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AssetBrandController extends Controller
{
    public function index(): View
    {
        return view('assets.brands', [
            'brands' => AssetBrand::query()->withCount('assets')->orderBy('name')->get(),
            'editBrand' => new AssetBrand(),
        ]);
    }

    public function store(AssetBrandRequest $request): RedirectResponse
    {
        AssetBrand::query()->create($request->validated() + ['is_active' => true]);

        return back()->with('success', 'Brand saved successfully.');
    }

    public function edit(AssetBrand $assetBrand): View
    {
        return view('assets.brands', [
            'brands' => AssetBrand::query()->withCount('assets')->orderBy('name')->get(),
            'editBrand' => $assetBrand,
        ]);
    }

    public function update(AssetBrandRequest $request, AssetBrand $assetBrand): RedirectResponse
    {
        $assetBrand->update($request->validated());

        return redirect()->route('asset-brands.index')->with('success', 'Brand updated successfully.');
    }

    public function toggleStatus(AssetBrand $assetBrand): RedirectResponse
    {
        $assetBrand->update([
            'is_active' => ! $assetBrand->is_active,
        ]);

        return back()->with('success', 'Brand status updated successfully.');
    }

    public function destroy(AssetBrand $assetBrand): RedirectResponse
    {
        $assetBrand->delete();

        return redirect()->route('asset-brands.index')->with('success', 'Brand deleted successfully.');
    }
}
