<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetBrandRequest;
use App\Models\AssetBrand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetBrandController extends Controller
{
    public function index(Request $request): View
    {
        $requestedPerPage = $request->integer('per_page', 10);
        $perPage = in_array($requestedPerPage, [10, 20, 30, 40, 50], true) ? $requestedPerPage : 10;
        $search = $request->string('search')->toString();
        $status = $request->input('status');

        return view('assets.brands', [
            'brands' => AssetBrand::query()
                ->withCount('assets')
                ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('is_active', $status === 'active'))
                ->orderBy('name')
                ->paginate($perPage)
                ->withQueryString(),
            'editBrand' => new AssetBrand(),
        ]);
    }

    public function store(AssetBrandRequest $request): RedirectResponse
    {
        AssetBrand::query()->create($request->validated() + ['is_active' => true]);

        return back()->with('success', 'Brand saved successfully.');
    }

    public function edit(Request $request, AssetBrand $assetBrand): View
    {
        $requestedPerPage = $request->integer('per_page', 10);
        $perPage = in_array($requestedPerPage, [10, 20, 30, 40, 50], true) ? $requestedPerPage : 10;
        $search = $request->string('search')->toString();
        $status = $request->input('status');

        return view('assets.brands', [
            'brands' => AssetBrand::query()
                ->withCount('assets')
                ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('is_active', $status === 'active'))
                ->orderBy('name')
                ->paginate($perPage)
                ->withQueryString(),
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
