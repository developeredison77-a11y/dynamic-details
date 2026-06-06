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
        return view('assets.brands', ['brands' => AssetBrand::query()->withCount('assets')->orderBy('name')->get()]);
    }

    public function store(AssetBrandRequest $request): RedirectResponse
    {
        AssetBrand::query()->create($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Brand saved successfully.');
    }
}
