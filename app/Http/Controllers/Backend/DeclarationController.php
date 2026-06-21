<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AssetAssignment;
use App\Models\AssetDeclaration;
use App\Models\AssetReturn;
use App\Services\DeclarationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DeclarationController extends Controller
{
    public function index(Request $request): View
    {
        $requestedHandoverPerPage = $request->integer('handover_per_page', 10);
        $handoverPerPage = in_array($requestedHandoverPerPage, [10, 20, 30, 40, 50], true) ? $requestedHandoverPerPage : 10;
        $requestedReturnPerPage = $request->integer('return_per_page', 10);
        $returnPerPage = in_array($requestedReturnPerPage, [10, 20, 30, 40, 50], true) ? $requestedReturnPerPage : 10;

        return view('declarations.index', [
            'declarations' => AssetDeclaration::query()
                ->with(['assignment.employee', 'assignment.asset'])
                ->when($request->string('handover_search')->toString(), function ($query, string $term): void {
                    $query->where(function ($query) use ($term): void {
                        $query->where('declaration_number', 'like', "%{$term}%")
                            ->orWhereHas('assignment.employee', function ($query) use ($term): void {
                                $query->where('employee_code', 'like', "%{$term}%")
                                    ->orWhere('name_en', 'like', "%{$term}%");
                            })
                            ->orWhereHas('assignment.asset', function ($query) use ($term): void {
                                $query->where('asset_tag', 'like', "%{$term}%")
                                    ->orWhere('name', 'like', "%{$term}%");
                            });
                    });
                })
                ->latest()
                ->paginate($handoverPerPage, ['*'], 'handover_page')
                ->withQueryString(),
            'returnDeclarations' => AssetReturn::query()
                ->with(['employee:id,employee_code,name_en', 'asset:id,asset_tag,name'])
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
                ->paginate($returnPerPage, ['id', 'employee_id', 'asset_id', 'returned_at', 'condition', 'signed_file_path', 'signed_file_name', 'signed_uploaded_at'], 'return_page')
                ->withQueryString(),
        ]);
    }

    public function store(AssetAssignment $assignment, DeclarationService $service): RedirectResponse
    {
        $declaration = $service->generate($assignment);

        $redirect = redirect()->route('declarations.show', $declaration);

        if (! $declaration->wasRecentlyCreated) {
            return $redirect;
        }

        return $redirect->with('success', 'Declaration generated successfully.');
    }

    public function show(AssetDeclaration $declaration): View
    {
        return view('declarations.show', ['declaration' => $declaration->load(['assignment.employee', 'assignment.asset.brand', 'assignment.asset.category'])]);
    }

    public function uploadSigned(Request $request, AssetDeclaration $declaration): RedirectResponse
    {
        $data = $request->validate([
            'signed_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($declaration->signed_file_path && Storage::disk('public')->exists($declaration->signed_file_path)) {
            Storage::disk('public')->delete($declaration->signed_file_path);
        }

        $file = $data['signed_file'];
        $path = $file->store('declarations/signed', 'public');

        $declaration->update([
            'signed_file_path' => $path,
            'signed_file_name' => $file->getClientOriginalName(),
            'signed_uploaded_at' => now(config('app.timezone', 'Asia/Kolkata')),
        ]);

        return back()->with('success', 'Signed declaration uploaded successfully.');
    }

    public function print(AssetDeclaration $declaration): View
    {
        return view('declarations.print', ['declaration' => $declaration->load(['assignment.employee', 'assignment.asset.brand', 'assignment.asset.category'])]);
    }
}
