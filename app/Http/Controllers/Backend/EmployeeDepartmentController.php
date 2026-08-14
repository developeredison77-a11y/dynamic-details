<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeDepartmentRequest;
use App\Models\EmployeeDepartment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeDepartmentController extends Controller
{
    public function index(Request $request): View
    {
        return view('employees.departments', [
            'departments' => $this->departmentQuery($request)->paginate($this->perPage($request))->withQueryString(),
            'editDepartment' => new EmployeeDepartment(),
        ]);
    }

    public function store(EmployeeDepartmentRequest $request): RedirectResponse
    {
        EmployeeDepartment::query()->create($request->validated() + ['is_active' => true]);

        return back()->with('success', 'Department saved successfully.');
    }

    public function edit(Request $request, EmployeeDepartment $employeeDepartment): View
    {
        return view('employees.departments', [
            'departments' => $this->departmentQuery($request)->paginate($this->perPage($request))->withQueryString(),
            'editDepartment' => $employeeDepartment,
        ]);
    }

    public function update(EmployeeDepartmentRequest $request, EmployeeDepartment $employeeDepartment): RedirectResponse
    {
        $employeeDepartment->update($request->validated());

        return redirect()->route('employee-departments.index')->with('success', 'Department updated successfully.');
    }

    public function toggleStatus(EmployeeDepartment $employeeDepartment): RedirectResponse
    {
        $employeeDepartment->update([
            'is_active' => ! $employeeDepartment->is_active,
        ]);

        return back()->with('success', 'Department status updated successfully.');
    }

    public function destroy(EmployeeDepartment $employeeDepartment): RedirectResponse
    {
        if ($employeeDepartment->employees()->exists()) {
            return back()->with('warning', 'Department cannot be deleted while employees are assigned to it.');
        }

        $employeeDepartment->delete();

        return redirect()->route('employee-departments.index')->with('success', 'Department deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $requestedPerPage = $request->integer('per_page', 10);

        return in_array($requestedPerPage, [10, 20, 30, 40, 50], true) ? $requestedPerPage : 10;
    }

    private function departmentQuery(Request $request)
    {
        $search = $request->string('search')->toString();
        $status = $request->input('status');

        return EmployeeDepartment::query()
            ->withCount('employees')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('is_active', $status === 'active'))
            ->orderBy('id', 'DESC');
    }
}
