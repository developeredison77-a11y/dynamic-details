<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Models\ImportBatch;
use App\Services\ImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function index(): View
    {
        return view('imports.index', ['batches' => ImportBatch::query()->latest()->paginate(10)]);
    }

    public function employees(ImportRequest $request, ImportService $service): RedirectResponse
    {
        $batch = $service->employees($request->file('file'), $request->user()?->id);

        return back()->with('success', "Employee import finished: {$batch->successful_rows} imported, {$batch->failed_rows} failed.");
    }

    public function assets(ImportRequest $request, ImportService $service): RedirectResponse
    {
        $batch = $service->assets($request->file('file'), $request->user()?->id);

        return back()->with('success', "Asset import finished: {$batch->successful_rows} imported, {$batch->failed_rows} failed.");
    }
}
