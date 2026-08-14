<?php

namespace App\Http\Controllers\Backend;

use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetRequest;
use App\Models\Asset;
use App\Models\AssetBrand;
use App\Models\AssetCategory;
use App\Services\AssetLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $requestedPerPage = $request->integer('per_page', 10);
        $perPage = in_array($requestedPerPage, [10, 20, 30, 40, 50], true) ? $requestedPerPage : 10;

        return view('assets.index', [
            'assets' => Asset::query()
                ->with([
                    'brand:id,name',
                    'category:id,name',
                    'activeAssignment' => fn ($query) => $query->select([
                        'asset_assignments.id',
                        'asset_assignments.asset_id',
                        'asset_assignments.employee_id',
                    ]),
                    'activeAssignment.employee:id,name_en',
                ])
                ->search($request->string('search')->toString())
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
                ->when($request->filled('category'), fn ($query) => $query->where('asset_category_id', $request->integer('category')))
                ->when($request->filled('brand'), fn ($query) => $query->where('asset_brand_id', $request->integer('brand')))
                ->latest()
                ->paginate($perPage)
                ->withQueryString(),
            'brands' => AssetBrand::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'categories' => AssetCategory::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => AssetStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('assets.form', $this->formData(new Asset()));
    }

    public function store(AssetRequest $request): RedirectResponse
    {
        Asset::query()->create($this->assetData($request));

        return redirect()->route('assets.index')->with('success', 'Asset created successfully.');
    }

    public function edit(Asset $asset): View
    {
        return view('assets.form', $this->formData($asset));
    }

    public function update(AssetRequest $request, Asset $asset, AssetLifecycleService $service): RedirectResponse
    {
        $oldInvoicePath = $asset->invoice_file_path;

        $service->updateAsset($asset, $this->assetData($request, $asset), $request->user()?->id);

        if ($oldInvoicePath && $request->hasFile('invoice_attachment')) {
            Storage::disk('local')->delete($oldInvoicePath);
        }

        return redirect()->route('assets.index')->with('success', 'Asset updated successfully.');
    }

    public function viewInvoice(Asset $asset): BinaryFileResponse
    {
        abort_unless($asset->invoice_file_path && Storage::disk('local')->exists($asset->invoice_file_path), 404);

        return response()->file(Storage::disk('local')->path($asset->invoice_file_path), [
            'Content-Type' => $asset->invoice_mime_type ?: 'application/octet-stream',
            'Content-Disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_INLINE,
                $asset->invoice_original_name ?: 'asset-invoice',
                Str::ascii($asset->invoice_original_name ?: 'asset-invoice') ?: 'asset-invoice'
            ),
        ]);
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        if ($asset->assignments()->exists()) {
            return back()->with('warning', 'Assets with handover history cannot be deleted. Retire the asset instead.');
        }

        $invoicePath = $asset->invoice_file_path;
        $asset->delete();

        if ($invoicePath) {
            Storage::disk('local')->delete($invoicePath);
        }

        return redirect()->route('assets.index')->with('success', 'Asset deleted successfully.');
    }

    private function formData(Asset $asset): array
    {
        return [
            'asset' => $asset,
            'brands' => AssetBrand::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'categories' => AssetCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'statuses' => AssetStatus::cases(),
            'conditions' => AssetCondition::cases(),
        ];
    }

    private function assetData(AssetRequest $request, ?Asset $asset = null): array
    {
        $data = $request->validated();
        unset($data['invoice_attachment']);

        $file = $request->file('invoice_attachment');

        if ($file) {
            $path = $file->store('asset-invoices', 'local');

            $data['invoice_file_path'] = $path;
            $data['invoice_original_name'] = $file->getClientOriginalName();
            $data['invoice_mime_type'] = $file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream';
            $data['invoice_file_size'] = $file->getSize() ?: 0;
        } elseif ($asset) {
            $data['invoice_file_path'] = $asset->invoice_file_path;
            $data['invoice_original_name'] = $asset->invoice_original_name;
            $data['invoice_mime_type'] = $asset->invoice_mime_type;
            $data['invoice_file_size'] = $asset->invoice_file_size;
        }

        return $data;
    }
}
