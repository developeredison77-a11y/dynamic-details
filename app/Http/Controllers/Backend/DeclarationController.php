<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AssetAssignment;
use App\Models\AssetDeclaration;
use App\Services\DeclarationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeclarationController extends Controller
{
    public function index(): View
    {
        return view('declarations.index', [
            'assignments' => AssetAssignment::query()->with(['employee', 'asset'])->assigned()->latest()->get(),
            'declarations' => AssetDeclaration::query()->with(['assignment.employee', 'assignment.asset'])->latest()->paginate(12),
        ]);
    }

    public function store(AssetAssignment $assignment, DeclarationService $service): RedirectResponse
    {
        $declaration = $service->generate($assignment);

        return redirect()->route('declarations.show', $declaration)->with('success', 'Declaration generated successfully.');
    }

    public function show(AssetDeclaration $declaration): View
    {
        return view('declarations.show', ['declaration' => $declaration->load(['assignment.employee', 'assignment.asset.brand', 'assignment.asset.category'])]);
    }

    public function print(AssetDeclaration $declaration): View
    {
        return view('declarations.print', ['declaration' => $declaration->load(['assignment.employee', 'assignment.asset.brand', 'assignment.asset.category'])]);
    }
}
