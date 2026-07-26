<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeDeclarationDocumentRequest;
use App\Models\AssetAssignment;
use App\Models\Employee;
use App\Models\EmployeeDeclarationDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class EmployeeDocumentController extends Controller
{
    public function handoverReport(Employee $employee): View
    {
        return view('employees.handover-report', $this->handoverReportData($employee, false, true));
    }

    public function handoverReportPrint(Employee $employee): View
    {
        return view('employees.handover-report', $this->handoverReportData($employee, true, false));
    }

    public function declarationForm(Employee $employee): View
    {
        return view('employees.declaration-form', $this->declarationFormData($employee, false, true));
    }

    public function declarationFormPrint(Employee $employee): View
    {
        return view('employees.declaration-form', $this->declarationFormData($employee, true, false));
    }

    public function uploadDeclarationDocument(EmployeeDeclarationDocumentRequest $request, Employee $employee): RedirectResponse
    {
        $file = $request->file('filled_declaration_file');
        $path = $file?->store("employee-declaration-documents/{$employee->id}", 'local');

        if (! $file || ! $path) {
            return back()->with('error', 'Unable to upload the filled declaration form. Please try again.');
        }

        $existingDocument = $employee->declarationDocument()->first();
        $oldPath = $existingDocument?->file_path;

        try {
            EmployeeDeclarationDocument::query()->updateOrCreate(
                ['employee_id' => $employee->id],
                [
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream',
                    'file_size' => $file->getSize() ?: 0,
                    'uploaded_by' => $request->user()?->id,
                    'uploaded_at' => now(config('app.timezone', 'Asia/Kolkata')),
                ]
            );
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            report($exception);

            return back()->with('error', 'Unable to save the uploaded declaration form. Please try again.');
        }

        if ($oldPath && $oldPath !== $path) {
            Storage::disk('local')->delete($oldPath);
        }

        return back()->with('success', $existingDocument ? 'Filled declaration form replaced successfully.' : 'Filled declaration form uploaded successfully.');
    }

    public function viewDeclarationDocument(Employee $employee): BinaryFileResponse
    {
        $document = $this->declarationDocumentFor($employee);
        $path = Storage::disk('local')->path($document->file_path);

        return response()->file($path, [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_INLINE,
                $document->original_name,
                Str::ascii($document->original_name) ?: 'filled-declaration-form'
            ),
        ]);
    }

    public function downloadDeclarationDocument(Employee $employee): StreamedResponse
    {
        $document = $this->declarationDocumentFor($employee);

        return Storage::disk('local')->download($document->file_path, $document->original_name, [
            'Content-Type' => $document->mime_type,
        ]);
    }

    private function handoverReportData(Employee $employee, bool $autoPrint, bool $showToolbar): array
    {
        $employee->loadMissing('role:id,name');

        return [
            'employee' => $employee,
            'assignments' => $employee->assignments()
                ->with([
                    'asset:id,asset_brand_id,asset_category_id,asset_tag,name,serial_number,model,status,condition',
                    'asset.brand:id,name',
                    'asset.category:id,name',
                    'creator:id,name',
                    'returnRecord:id,asset_assignment_id,returned_at,condition,notes',
                ])
                ->orderByDesc('handover_date')
                ->orderByDesc('id')
                ->get(),
            'autoPrint' => $autoPrint,
            'showToolbar' => $showToolbar,
        ];
    }

    private function declarationFormData(Employee $employee, bool $autoPrint, bool $showToolbar): array
    {
        $employee->loadMissing('role:id,name');

        $assignments = $employee->activeAssignments()
            ->with([
                'asset:id,asset_brand_id,asset_category_id,asset_tag,name,serial_number,model,status,condition',
                'asset.brand:id,name',
                'asset.category:id,name',
            ])
            ->orderBy('handover_date')
            ->orderBy('id')
            ->get();

        return [
            'employee' => $employee,
            'assignments' => $assignments,
            'assetSummary' => $this->assetSummary($assignments),
            'autoPrint' => $autoPrint,
            'showToolbar' => $showToolbar,
        ];
    }

    private function declarationDocumentFor(Employee $employee): EmployeeDeclarationDocument
    {
        $document = $employee->declarationDocument()->firstOrFail();

        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return $document;
    }

    /**
     * @param  Collection<int, AssetAssignment>  $assignments
     * @return array{english: string, arabic: string, date: string}
     */
    private function assetSummary(Collection $assignments): array
    {
        $date = $assignments
            ->pluck('handover_date')
            ->filter()
            ->min()
            ?->format('d/m/Y') ?? '____/____/______';

        if ($assignments->isEmpty()) {
            return [
                'english' => 'the following company asset(s): ______________________________',
                'arabic' => 'أصول الشركة التالية: ______________________________',
                'date' => $date,
            ];
        }

        $visibleAssignments = $assignments->take(3);
        $remainingCount = max(0, $assignments->count() - $visibleAssignments->count());

        $english = $visibleAssignments
            ->map(function ($assignment): string {
                $asset = $assignment->asset;

                return trim(collect([
                    $asset?->category?->name,
                    $asset?->brand?->name,
                    $asset?->model,
                    $asset?->asset_tag ? "({$asset->asset_tag})" : null,
                ])->filter()->implode(' '));
            })
            ->filter()
            ->implode('; ');

        $arabic = $visibleAssignments
            ->map(function ($assignment): string {
                $asset = $assignment->asset;

                return trim(collect([
                    $asset?->category?->name,
                    $asset?->brand?->name,
                    $asset?->model,
                    $asset?->asset_tag ? "({$asset->asset_tag})" : null,
                ])->filter()->implode(' '));
            })
            ->filter()
            ->implode('؛ ');

        if ($remainingCount > 0) {
            $english .= " and {$remainingCount} more company asset".($remainingCount === 1 ? '' : 's');
            $arabic .= " و {$remainingCount} من أصول الشركة الأخرى";
        }

        return [
            'english' => $english !== '' ? $english : 'the listed company asset(s)',
            'arabic' => $arabic !== '' ? $arabic : 'أصول الشركة المدرجة',
            'date' => $date,
        ];
    }
}
