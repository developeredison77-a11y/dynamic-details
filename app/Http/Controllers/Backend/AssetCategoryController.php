<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCategoryRequest;
use App\Models\AssetCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetCategoryController extends Controller
{
    public function index(Request $request): View
    {
        return view('assets.categories', [
            'categories' => $this->categoryQuery($request)->paginate($this->perPage($request))->withQueryString(),
            'editCategory' => new AssetCategory(),
        ]);
    }

    public function store(AssetCategoryRequest $request): RedirectResponse
    {
        AssetCategory::query()->create($request->validated() + ['is_active' => true]);

        return back()->with('success', 'Category saved successfully.');
    }

    public function edit(Request $request, int $assetCategory): View
    {
        $editCategory = AssetCategory::query()->findOrFail($assetCategory);

        return view('assets.categories', [
            'categories' => $this->categoryQuery($request)->paginate($this->perPage($request))->withQueryString(),
            'editCategory' => $editCategory,
        ]);
    }

    public function update(AssetCategoryRequest $request, AssetCategory $assetCategory): RedirectResponse
    {
        $assetCategory->update($request->validated());

        return redirect()->route('asset-categories.index')->with('success', 'Category updated successfully.');
    }

    public function toggleStatus(AssetCategory $assetCategory): RedirectResponse
    {
        $assetCategory->update([
            'is_active' => ! $assetCategory->is_active,
        ]);

        return back()->with('success', 'Category status updated successfully.');
    }

    public function destroy(AssetCategory $assetCategory): RedirectResponse
    {
        $assetCategory->delete();

        return redirect()->route('asset-categories.index')->with('success', 'Category deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $requestedPerPage = $request->integer('per_page', 10);

        return in_array($requestedPerPage, [10, 20, 30, 40, 50], true) ? $requestedPerPage : 10;
    }

    private function categoryQuery(Request $request)
    {
        $search = $request->string('search')->toString();
        $status = $request->input('status');

        return AssetCategory::query()
            ->withCount('assets')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('is_active', $status === 'active'))
            ->orderBy('name');
    }
}
