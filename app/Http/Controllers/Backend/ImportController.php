<?php

namespace App\Http\Controllers\Backend;

use App\Enums\AssetCondition;
use App\Enums\ImportType;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Models\AssetBrand;
use App\Models\AssetCategory;
use App\Models\ImportBatch;
use App\Services\ImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function index(Request $request): View
    {
        return view('imports.index', [
            'batches' => ImportBatch::query()
                ->latest()
                ->paginate($this->perPage($request))
                ->withQueryString(),
            ...$this->assetImportReferences(),
        ]);
    }

    public function employeeIndex(Request $request): View
    {
        return view('imports.employees', [
            'batches' => $this->batchesFor(ImportType::Employees, $request),
        ]);
    }

    public function assetIndex(Request $request): View
    {
        return view('imports.assets', [
            'batches' => $this->batchesFor(ImportType::Assets, $request),
            ...$this->assetImportReferences(),
        ]);
    }

    public function employees(Request $request, ImportService $service): RedirectResponse
    {
        if ($response = $this->invalidUploadResponse($request, 'employeeImport')) {
            return $response;
        }

        $request->validateWithBag('employeeImport', ImportRequest::importRules(), ImportRequest::importMessages());
        $batch = $service->employees($request->file('file'), $request->user()?->id);

        if ($batch->total_rows === 0) {
            return back()
                ->withErrors(['rows' => $this->importErrorSummary($batch)], 'employeeImport')
                ->with('warning', 'Employee import failed: no data rows found.');
        }

        if ($batch->failed_rows > 0) {
            return back()
                ->withErrors(['rows' => $this->importErrorSummary($batch)], 'employeeImport')
                ->with('warning', "Employee import finished with validation errors: {$batch->successful_rows} imported, {$batch->failed_rows} failed.");
        }

        return back()->with('success', "Employee import finished: {$batch->successful_rows} imported, {$batch->failed_rows} failed.");
    }

    public function assets(Request $request, ImportService $service): RedirectResponse
    {
        if ($response = $this->invalidUploadResponse($request, 'assetImport')) {
            return $response;
        }

        $request->validateWithBag('assetImport', ImportRequest::importRules(), ImportRequest::importMessages());
        $batch = $service->assets($request->file('file'), $request->user()?->id);

        if ($batch->total_rows === 0) {
            return back()
                ->withErrors(['rows' => $this->importErrorSummary($batch)], 'assetImport')
                ->with('warning', 'Asset import failed: no data rows found.');
        }

        if ($batch->failed_rows > 0) {
            return back()
                ->withErrors(['rows' => $this->importErrorSummary($batch)], 'assetImport')
                ->with('warning', "Asset import finished with validation errors: {$batch->successful_rows} imported, {$batch->failed_rows} failed.");
        }

        return back()->with('success', "Asset import finished: {$batch->successful_rows} imported, {$batch->failed_rows} failed.");
    }

    public function template(string $type): Response
    {
        $templates = [
            'employees' => [
                'name_en',
                'name_ar',
                'email',
                'department',
                'designation',
                'phone',
                'status',
            ],
            'assets' => [
                'asset_tag',
                'name',
                'asset_category_id',
                'asset_brand_id',
                'serial_number',
                'model',
                'condition',
            ],
        ];

        abort_unless(isset($templates[$type]), 404);

        $content = implode(',', $templates[$type]).PHP_EOL;

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$type}-import-template.csv\"",
        ]);
    }

    private function invalidUploadResponse(Request $request, string $bag): ?RedirectResponse
    {
        $file = $request->allFiles()['file'] ?? null;

        if (! $file instanceof UploadedFile || $file->isValid()) {
            return null;
        }

        return back()->withErrors([
            'file' => $this->uploadErrorMessage($file->getError()),
        ], $bag);
    }

    private function batchesFor(ImportType $type, Request $request)
    {
        return ImportBatch::query()
            ->where('type', $type->value)
            ->latest()
            ->paginate($this->perPage($request))
            ->withQueryString();
    }

    private function perPage(Request $request): int
    {
        $requestedPerPage = $request->integer('per_page', 10);

        return in_array($requestedPerPage, [10, 20, 30, 40, 50], true) ? $requestedPerPage : 10;
    }

    private function uploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The selected file is larger than the server upload limit. Try a smaller file or increase the PHP upload limit.',
            UPLOAD_ERR_PARTIAL => 'The file upload was interrupted before it finished. Please try again.',
            UPLOAD_ERR_NO_FILE => 'Please upload a file before starting the import.',
            UPLOAD_ERR_NO_TMP_DIR => 'File uploads are not available because the server upload temp directory is not configured.',
            UPLOAD_ERR_CANT_WRITE => 'The server could not write the uploaded file to disk. Please check folder permissions.',
            UPLOAD_ERR_EXTENSION => 'A server extension stopped the file upload. Please check the PHP server configuration.',
            default => 'The file could not be uploaded because of a server-side upload error.',
        };
    }

    private function importErrorSummary(ImportBatch $batch): array
    {
        if ($batch->total_rows === 0) {
            return ['No data rows found. Please upload a file with a header row and at least one data row.'];
        }

        return collect($batch->errors ?? [])
            ->take(5)
            ->map(function (array $error): string {
                $messages = collect($error['messages'] ?? [])
                    ->filter()
                    ->implode(' ');

                return "Row {$error['row']}: {$messages}";
            })
            ->when(
                $batch->failed_rows > 5,
                fn ($messages) => $messages->push('More row errors are available in Import History.')
            )
            ->all();
    }

    private function assetImportReferences(): array
    {
        return [
            'assetCategories' => AssetCategory::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'assetBrands' => AssetBrand::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'assetConditions' => AssetCondition::cases(),
        ];
    }
}
