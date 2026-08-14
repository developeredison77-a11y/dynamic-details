<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeJobRequest;
use App\Models\EmployeeJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeJobController extends Controller
{
    public function index(Request $request): View
    {
        return view('employees.jobs', [
            'jobs' => $this->jobQuery($request)->paginate($this->perPage($request))->withQueryString(),
            'editJob' => new EmployeeJob(),
        ]);
    }

    public function store(EmployeeJobRequest $request): RedirectResponse
    {
        EmployeeJob::query()->create($request->validated() + ['is_active' => true]);

        return back()->with('success', 'Job saved successfully.');
    }

    public function edit(Request $request, EmployeeJob $employeeJob): View
    {
        return view('employees.jobs', [
            'jobs' => $this->jobQuery($request)->paginate($this->perPage($request))->withQueryString(),
            'editJob' => $employeeJob,
        ]);
    }

    public function update(EmployeeJobRequest $request, EmployeeJob $employeeJob): RedirectResponse
    {
        $employeeJob->update($request->validated());

        return redirect()->route('employee-jobs.index')->with('success', 'Job updated successfully.');
    }

    public function toggleStatus(EmployeeJob $employeeJob): RedirectResponse
    {
        $employeeJob->update([
            'is_active' => ! $employeeJob->is_active,
        ]);

        return back()->with('success', 'Job status updated successfully.');
    }

    public function destroy(EmployeeJob $employeeJob): RedirectResponse
    {
        if ($employeeJob->employees()->exists()) {
            return back()->with('warning', 'Job cannot be deleted while employees are assigned to it.');
        }

        $employeeJob->delete();

        return redirect()->route('employee-jobs.index')->with('success', 'Job deleted successfully.');
    }

    private function perPage(Request $request): int
    {
        $requestedPerPage = $request->integer('per_page', 10);

        return in_array($requestedPerPage, [10, 20, 30, 40, 50], true) ? $requestedPerPage : 10;
    }

    private function jobQuery(Request $request)
    {
        $search = $request->string('search')->toString();
        $status = $request->input('status');

        return EmployeeJob::query()
            ->withCount('employees')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('is_active', $status === 'active'))
            ->orderBy('id', 'DESC');
    }
}
