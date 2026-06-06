<?php

namespace App\Http\Controllers\Backend;

use App\Enums\EmployeeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        return view('employees.index', [
            'employees' => Employee::query()
                ->withCount(['activeAssignments'])
                ->search($request->string('search')->toString())
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
                ->latest()
                ->paginate(12)
                ->withQueryString(),
            'statuses' => EmployeeStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('employees.form', ['employee' => new Employee(), 'statuses' => EmployeeStatus::cases()]);
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        Employee::query()->create($request->validated());

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit(Employee $employee): View
    {
        return view('employees.form', ['employee' => $employee, 'statuses' => EmployeeStatus::cases()]);
    }

    public function update(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $data = $request->validated();

        if ($employee->status?->value !== $data['status']) {
            $data['status_changed_at'] = now()->toDateString();
        }

        $employee->update($data);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        if ($employee->assignments()->exists()) {
            return back()->with('warning', 'Employees with asset history cannot be deleted. Change the status instead.');
        }

        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }
}
