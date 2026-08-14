<?php

namespace App\Http\Controllers\Backend;

use App\Enums\EmployeeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Models\EmployeeDepartment;
use App\Models\EmployeeJob;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $requestedPerPage = $request->integer('per_page', 10);
        $perPage = in_array($requestedPerPage, [10, 20, 30, 40, 50], true) ? $requestedPerPage : 10;

        return view('employees.index', [
            'employees' => Employee::query()
                ->with([
                    'role:id,name',
                    'employeeDepartment:id,name',
                    'employeeJob:id,name',
                    'declarationDocument:id,employee_id,original_name,mime_type,file_size,uploaded_at',
                ])
                ->withCount(['activeAssignments'])
                ->search($request->string('search')->toString())
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
                ->when($request->filled('role_id'), fn ($query) => $query->where('role_id', $request->integer('role_id')))
                ->latest()
                ->paginate($perPage)
                ->withQueryString(),
            'statuses' => EmployeeStatus::cases(),
            'roles' => Role::query()->active()->orderBy('name')->get(['id', 'name']),
            'departments' => EmployeeDepartment::query()->active()->orderBy('name')->get(['id', 'name']),
            'jobs' => EmployeeJob::query()->active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        $draftCode = session('draft_employee_code');

        if (blank($draftCode) || ! preg_match('/^ADMS\d{6}$/', (string) $draftCode)) {
            $draftCode = Employee::generateEmployeeCode();
        }

        $employee = new Employee();
        $employee->employee_code = $draftCode;

        session(['draft_employee_code' => $employee->employee_code]);

        return view('employees.form', [
            'employee' => $employee,
            'statuses' => EmployeeStatus::cases(),
            'roles' => Role::query()->active()->orderBy('name')->get(['id', 'name']),
            'departments' => EmployeeDepartment::query()->active()->orderBy('name')->get(['id', 'name']),
            'jobs' => EmployeeJob::query()->active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        $data = $this->employeeData($request);
        $employee = new Employee($data);
        $draftCode = session()->pull('draft_employee_code');
        $employee->employee_code = filled($draftCode)
            && preg_match('/^ADMS\d{6}$/', (string) $draftCode)
            && ! Employee::withTrashed()->where('employee_code', $draftCode)->exists()
            ? $draftCode
            : Employee::generateEmployeeCode();
        $employee->save();

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee created successfully.')
            ->with('generated_employee_code', $employee->employee_code);
    }

    public function show(Employee $employee): View
    {
        $employee->load([
            'role:id,name',
            'employeeDepartment:id,name',
            'employeeJob:id,name',
            'declarationDocument:id,employee_id,original_name,mime_type,file_size,uploaded_at',
        ])->loadCount([
            'assignments',
            'activeAssignments',
            'assignments as returned_assignments_count' => fn (Builder $query) => $query->where('status', 'returned'),
        ]);

        return view('employees.show', [
            'employee' => $employee,
            'assignments' => $employee->assignments()
                ->with(['asset:id,asset_tag,name,model,status,condition,asset_category_id,asset_brand_id', 'asset.category:id,name', 'asset.brand:id,name'])
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }

    public function edit(Employee $employee): View
    {
        return view('employees.form', [
            'employee' => $employee,
            'statuses' => EmployeeStatus::cases(),
            'roles' => Role::query()->active()->orderBy('name')->get(['id', 'name']),
            'departments' => EmployeeDepartment::query()->active()->orderBy('name')->get(['id', 'name']),
            'jobs' => EmployeeJob::query()->active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $data = $this->employeeData($request);

        if ($employee->status?->value !== $data['status']) {
            $data['status_changed_at'] = now()->toDateString();
        }

        $employee->update($data);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }

    private function employeeData(EmployeeRequest $request): array
    {
        $data = $request->validated();
        $department = filled($data['employee_department_id'] ?? null)
            ? EmployeeDepartment::query()->find($data['employee_department_id'])
            : null;
        $job = filled($data['employee_job_id'] ?? null)
            ? EmployeeJob::query()->find($data['employee_job_id'])
            : null;
        $data['department'] = $department?->name;
        $data['designation'] = $job?->name;

        return $data;
    }
}
