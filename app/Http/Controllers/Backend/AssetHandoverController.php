<?php

namespace App\Http\Controllers\Backend;

use App\Enums\EmployeeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetHandoverRequest;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Employee;
use App\Services\AssetLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AssetHandoverController extends Controller
{
    public function index(): View
    {
        return view('asset-handovers.index', [
            'assignments' => AssetAssignment::query()
                ->with([
                    'employee:id,employee_code,name_en',
                    'asset:id,asset_brand_id,asset_category_id,asset_tag,name',
                    'asset.brand:id,name',
                    'asset.category:id,name',
                ])
                ->latest()
                ->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('asset-handovers.create', [
            'employees' => Employee::query()
                ->where('status', EmployeeStatus::Active)
                ->orderBy('name_en')
                ->get(['id', 'employee_code', 'name_en']),
            'assets' => Asset::query()
                ->available()
                ->with(['brand:id,name', 'category:id,name'])
                ->orderBy('asset_tag')
                ->get(['id', 'asset_brand_id', 'asset_category_id', 'asset_tag', 'name']),
        ]);
    }

    public function store(AssetHandoverRequest $request, AssetLifecycleService $service): RedirectResponse
    {
        try {
            $assignment = $service->handover($request->validated(), $request->user()?->id);
        } catch (\DomainException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('asset-handovers.show', $assignment)->with('success', 'Asset handover saved successfully.');
    }

    public function show(AssetAssignment $assetHandover): View
    {
        return view('asset-handovers.show', ['assignment' => $assetHandover->load([
            'employee:id,employee_code,name_en,name_ar',
            'asset:id,asset_brand_id,asset_category_id,asset_tag,name,serial_number',
            'asset.brand:id,name',
            'asset.category:id,name',
            'declaration:id,asset_assignment_id,declaration_number',
        ])]);
    }

    public function print(AssetAssignment $assetHandover): View
    {
        return view('asset-handovers.print', ['assignment' => $assetHandover->load([
            'employee:id,employee_code,name_en,name_ar',
            'asset:id,asset_brand_id,asset_category_id,asset_tag,name,serial_number',
            'asset.brand:id,name',
            'asset.category:id,name',
        ])]);
    }
}
