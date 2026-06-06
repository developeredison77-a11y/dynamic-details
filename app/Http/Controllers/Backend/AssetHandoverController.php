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
            'assignments' => AssetAssignment::query()->with(['employee', 'asset.brand', 'asset.category'])->latest()->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('asset-handovers.create', [
            'employees' => Employee::query()->where('status', EmployeeStatus::Active)->orderBy('name_en')->get(),
            'assets' => Asset::query()->available()->with(['brand', 'category'])->orderBy('asset_tag')->get(),
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
        return view('asset-handovers.show', ['assignment' => $assetHandover->load(['employee', 'asset.brand', 'asset.category', 'declaration'])]);
    }

    public function print(AssetAssignment $assetHandover): View
    {
        return view('asset-handovers.print', ['assignment' => $assetHandover->load(['employee', 'asset.brand', 'asset.category'])]);
    }
}
