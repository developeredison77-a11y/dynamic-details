<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCategoryRequest;
use App\Models\AssetCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AssetCategoryController extends Controller
{
    public function index(): View
    {
        return view('assets.categories', ['categories' => AssetCategory::query()->withCount('assets')->orderBy('name')->get()]);
    }

    public function store(AssetCategoryRequest $request): RedirectResponse
    {
        AssetCategory::query()->create($request->validated() + [
            'requires_serial' => $request->boolean('requires_serial'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Category saved successfully.');
    }
}
