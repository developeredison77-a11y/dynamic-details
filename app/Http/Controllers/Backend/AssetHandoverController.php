<?php

namespace App\Http\Controllers\Backend;

use App\Enums\AssetAssignmentStatus;
use App\Enums\EmployeeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetHandoverRequest;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\Employee;
use App\Services\AssetLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetHandoverController extends Controller
{
    public function index(Request $request): View
    {
        $requestedPerPage = $request->integer('per_page', 10);
        $perPage = in_array($requestedPerPage, [10, 20, 30, 40, 50], true) ? $requestedPerPage : 10;

        return view('asset-handovers.index', [
            'assignments' => AssetAssignment::query()
                ->with([
                    'employee:id,employee_code,name_en',
                    'asset:id,asset_brand_id,asset_category_id,asset_tag,name',
                    'asset.brand:id,name',
                    'asset.category:id,name',
                ])
                ->when($request->string('search')->toString(), function ($query, string $term): void {
                    $query->where(function ($query) use ($term): void {
                        $query->whereHas('employee', function ($query) use ($term): void {
                            $query->where('employee_code', 'like', "%{$term}%")
                                ->orWhere('name_en', 'like', "%{$term}%");
                        })
                            ->orWhereHas('asset', function ($query) use ($term): void {
                                $query->where('asset_tag', 'like', "%{$term}%")
                                    ->orWhere('name', 'like', "%{$term}%")
                                    ->orWhereHas('brand', fn ($query) => $query->where('name', 'like', "%{$term}%"))
                                    ->orWhereHas('category', fn ($query) => $query->where('name', 'like', "%{$term}%"));
                            });
                    });
                })
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
                ->when($request->filled('category'), function ($query) use ($request): void {
                    $query->whereHas('asset', fn ($query) => $query->where('asset_category_id', $request->integer('category')));
                })
                ->latest()
                ->paginate($perPage)
                ->withQueryString(),
            'categories' => AssetCategory::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => AssetAssignmentStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('asset-handovers.create', $this->formData(new AssetAssignment()));
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

    public function edit(AssetAssignment $assetHandover): View|RedirectResponse
    {
        if (! $assetHandover->canBeEdited()) {
            return redirect()
                ->route('asset-handovers.show', $assetHandover)
                ->with('warning', 'This handover can be edited only before the handover date starts.');
        }

        return view('asset-handovers.create', $this->formData($assetHandover));
    }

    public function update(AssetHandoverRequest $request, AssetAssignment $assetHandover, AssetLifecycleService $service): RedirectResponse
    {
        try {
            $assignment = $service->updateHandover($assetHandover, $request->validated());
        } catch (\DomainException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('asset-handovers.show', $assignment)->with('success', 'Asset handover updated successfully.');
    }

    public function show(AssetAssignment $assetHandover): View
    {
        return view('asset-handovers.show', ['assignment' => $assetHandover->load([
            'employee:id,employee_code,name_en,name_ar',
            'asset:id,asset_brand_id,asset_category_id,asset_tag,name,serial_number,model,status,condition',
            'asset.brand:id,name',
            'asset.category:id,name',
            'creator:id,name',
            'declaration:id,asset_assignment_id,declaration_number',
            'returnRecord:id,asset_assignment_id,returned_at,condition,notes',
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

    private function formData(AssetAssignment $assignment): array
    {
        return [
            'assignment' => $assignment,
            'employees' => Employee::query()
                ->where('status', EmployeeStatus::Active)
                ->orderBy('name_en')
                ->get(['id', 'employee_code', 'name_en']),
            'assets' => Asset::query()
                ->where(function ($query) use ($assignment): void {
                    $query->available();

                    if ($assignment->exists) {
                        $query->orWhere('assets.id', $assignment->asset_id);
                    }
                })
                ->with(['brand:id,name', 'category:id,name'])
                ->orderBy('asset_tag')
                ->get(['id', 'asset_brand_id', 'asset_category_id', 'asset_tag', 'name']),
        ];
    }
}
