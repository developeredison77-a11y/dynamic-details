<?php

namespace App\Http\Controllers\Backend;

use App\Enums\AssetCondition;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetReturnRequest;
use App\Models\AssetAssignment;
use App\Models\AssetReturn;
use App\Services\AssetLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AssetReturnController extends Controller
{
    public function index(Request $request): View
    {
        $requestedReturnPerPage = $request->integer('return_per_page', 10);
        $returnPerPage = in_array($requestedReturnPerPage, [10, 20, 30, 40, 50], true) ? $requestedReturnPerPage : 10;
        $requestedHistoryPerPage = $request->integer('history_per_page', 10);
        $historyPerPage = in_array($requestedHistoryPerPage, [10, 20, 30, 40, 50], true) ? $requestedHistoryPerPage : 10;

        return view('asset-returns.index', [
            'assignments' => AssetAssignment::query()
                ->assigned()
                ->with([
                    'employee:id,employee_code,name_en',
                    'asset:id,asset_tag,name',
                ])
                ->when($request->string('return_search')->toString(), function ($query, string $term): void {
                    $query->where(function ($query) use ($term): void {
                        $query->whereHas('employee', function ($query) use ($term): void {
                            $query->where('employee_code', 'like', "%{$term}%")
                                ->orWhere('name_en', 'like', "%{$term}%");
                        })
                            ->orWhereHas('asset', function ($query) use ($term): void {
                                $query->where('asset_tag', 'like', "%{$term}%")
                                    ->orWhere('name', 'like', "%{$term}%");
                            });
                    });
                })
                ->latest()
                ->paginate($returnPerPage, ['id', 'employee_id', 'asset_id', 'status', 'handover_date', 'expected_return_date', 'created_at'], 'return_page')
                ->withQueryString(),
            'returns' => AssetReturn::query()
                ->with([
                    'employee:id,employee_code,name_en',
                    'asset:id,asset_tag,name',
                ])
                ->when($request->string('history_search')->toString(), function ($query, string $term): void {
                    $query->where(function ($query) use ($term): void {
                        $query->whereHas('employee', function ($query) use ($term): void {
                            $query->where('employee_code', 'like', "%{$term}%")
                                ->orWhere('name_en', 'like', "%{$term}%");
                        })
                            ->orWhereHas('asset', function ($query) use ($term): void {
                                $query->where('asset_tag', 'like', "%{$term}%")
                                    ->orWhere('name', 'like', "%{$term}%");
                            });
                    });
                })
                ->when($request->filled('history_condition'), fn ($query) => $query->where('condition', $request->input('history_condition')))
                ->latest()
                ->paginate($historyPerPage, ['id', 'employee_id', 'asset_id', 'returned_at', 'condition', 'signed_file_path', 'signed_file_name', 'signed_uploaded_at'], 'history_page')
                ->withQueryString(),
            'conditions' => AssetCondition::cases(),
        ]);
    }

    public function store(AssetReturnRequest $request, AssetAssignment $assignment, AssetLifecycleService $service): RedirectResponse
    {
        try {
            $service->returnAsset($assignment, $request->validated(), $request->user()?->id);
        } catch (\DomainException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('asset-returns.index')->with('success', 'Asset return saved successfully.');
    }

    public function uploadSigned(Request $request, AssetReturn $assetReturn): RedirectResponse
    {
        $data = $request->validate([
            'signed_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($assetReturn->signed_file_path && Storage::disk('public')->exists($assetReturn->signed_file_path)) {
            Storage::disk('public')->delete($assetReturn->signed_file_path);
        }

        $file = $data['signed_file'];
        $path = $file->store('asset-returns/signed', 'public');

        $assetReturn->update([
            'signed_file_path' => $path,
            'signed_file_name' => $file->getClientOriginalName(),
            'signed_uploaded_at' => now(config('app.timezone', 'Asia/Kolkata')),
        ]);

        return back()->with('success', 'Signed return document uploaded successfully.');
    }

    public function print(AssetReturn $assetReturn): View
    {
        return view('asset-returns.print', ['return' => $assetReturn->load([
            'employee:id,employee_code,name_en',
            'asset:id,asset_brand_id,asset_category_id,asset_tag,name,serial_number',
            'asset.brand:id,name',
            'asset.category:id,name',
        ])]);
    }
}
