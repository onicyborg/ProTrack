<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Models\ProjectManager;
use App\Models\ProjectRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::query()
            ->with(['client', 'projectManagers.pm'])
            ->latest()
            ->get();

        $clients = Client::query()->orderBy('client_name')->get();

        $pms = Employee::query()
            ->whereHas('user', function ($q) {
                $q->where('role', 'pm');
            })
            ->orderBy('employee_name')
            ->get();

        return view('admin.project.index', compact('projects', 'clients', 'pms'));
    }

    public function show(string $id)
    {
        $project = Project::query()
            ->with([
                'client',
                'projectManagers.employee',
                'roles',
                'projectManagers.projectEmployees.employee',
                'projectManagers.projectEmployees.projectRole',
            ])
            ->findOrFail($id);

        $employees = Employee::query()
            ->whereNull('user_id')
            ->orderBy('employee_name')
            ->get();

        $roles = ProjectRole::query()
            ->where('project_id', $project->id)
            ->orderBy('role_name')
            ->get();

        return view('admin.project.show', compact('project', 'employees', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_name' => ['required', 'string', 'max:255'],
            'client_id' => ['required', 'uuid', 'exists:clients,id'],
            'account_code' => ['nullable', 'string', 'max:255'],
            'budget_year' => ['nullable', 'string', 'max:20'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['active', 'completed', 'on_hold'])],
            'pm_ids' => ['required', 'array', 'min:1'],
            'pm_ids.*' => ['required', 'uuid', 'exists:employees,id'],
        ]);

        $pmIds = array_values(array_unique($validated['pm_ids']));

        $pmCount = Employee::query()
            ->whereIn('id', $pmIds)
            ->whereHas('user', function ($q) {
                $q->where('role', 'pm');
            })
            ->count();

        if ($pmCount !== count($pmIds)) {
            throw ValidationException::withMessages([
                'pm_ids' => 'PM yang dipilih tidak valid.',
            ]);
        }

        DB::transaction(function () use ($validated, $pmIds) {
            $project = Project::create([
                'project_name' => $validated['project_name'],
                'client_id' => $validated['client_id'],
                'account_code' => $validated['account_code'] ?? null,
                'budget_year' => $validated['budget_year'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'status' => $validated['status'],
            ]);

            foreach ($pmIds as $pmId) {
                ProjectManager::create([
                    'project_id' => $project->id,
                    'pm_id' => $pmId,
                ]);
            }
        });

        return redirect()->route('admin.projects.index')->with('success', 'Proyek berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $project = Project::query()->with('projectManagers')->findOrFail($id);

        $validated = $request->validate([
            'project_name' => ['required', 'string', 'max:255'],
            'client_id' => ['required', 'uuid', 'exists:clients,id'],
            'account_code' => ['nullable', 'string', 'max:255'],
            'budget_year' => ['nullable', 'string', 'max:20'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['active', 'completed', 'on_hold'])],
            'pm_ids' => ['required', 'array', 'min:1'],
            'pm_ids.*' => ['required', 'uuid', 'exists:employees,id'],
        ]);

        $pmIds = array_values(array_unique($validated['pm_ids']));

        $pmCount = Employee::query()
            ->whereIn('id', $pmIds)
            ->whereHas('user', function ($q) {
                $q->where('role', 'pm');
            })
            ->count();

        if ($pmCount !== count($pmIds)) {
            throw ValidationException::withMessages([
                'pm_ids' => 'PM yang dipilih tidak valid.',
            ]);
        }

        DB::transaction(function () use ($project, $validated, $pmIds) {
            $project->update([
                'project_name' => $validated['project_name'],
                'client_id' => $validated['client_id'],
                'account_code' => $validated['account_code'] ?? null,
                'budget_year' => $validated['budget_year'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'status' => $validated['status'],
            ]);

            ProjectManager::query()->where('project_id', $project->id)->delete();

            foreach ($pmIds as $pmId) {
                ProjectManager::create([
                    'project_id' => $project->id,
                    'pm_id' => $pmId,
                ]);
            }
        });

        return redirect()->route('admin.projects.index')->with('success', 'Proyek berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $project = Project::query()->with('projectManagers')->findOrFail($id);

        DB::transaction(function () use ($project) {
            $project->projectManagers()->delete();
            $project->delete();
        });

        return redirect()->route('admin.projects.index')->with('success', 'Proyek berhasil dihapus.');
    }

    public function storeRole(Request $request, string $projectId)
    {
        $project = Project::query()->findOrFail($projectId);

        $validated = $request->validate([
            'role_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('project_roles', 'role_name')
                    ->where('project_id', $project->id)
                    ->whereNull('deleted_at'),
            ],
        ]);

        ProjectRole::create([
            'project_id' => $project->id,
            'role_name' => $validated['role_name'],
        ]);

        return redirect()->route('admin.projects.show', $project->id)->with('success', 'Role proyek berhasil ditambahkan.');
    }

    public function updateRole(Request $request, string $projectId, string $roleId)
    {
        $project = Project::query()->findOrFail($projectId);
        $role = ProjectRole::query()->where('project_id', $project->id)->findOrFail($roleId);

        $validated = $request->validate([
            'role_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('project_roles', 'role_name')
                    ->where('project_id', $project->id)
                    ->whereNull('deleted_at')
                    ->ignore($role->id),
            ],
        ]);

        $role->update([
            'role_name' => $validated['role_name'],
        ]);

        return redirect()->route('admin.projects.show', $project->id)->with('success', 'Role proyek berhasil diperbarui.');
    }

    public function destroyRole(string $projectId, string $roleId)
    {
        $project = Project::query()->findOrFail($projectId);
        $role = ProjectRole::query()->where('project_id', $project->id)->findOrFail($roleId);

        $role->delete();

        return redirect()->route('admin.projects.show', $project->id)->with('success', 'Role proyek berhasil dihapus.');
    }

    public function assignEmployee(Request $request, string $projectId)
    {
        $project = Project::query()->with('projectManagers')->findOrFail($projectId);

        $validated = $request->validate([
            'project_manager_id' => ['required', 'uuid', 'exists:project_managers,id'],
            'employee_id' => ['required', 'uuid', 'exists:employees,id'],
            'project_role_id' => ['required', 'uuid', 'exists:project_roles,id'],
        ]);

        $pmLink = ProjectManager::query()
            ->where('project_id', $project->id)
            ->findOrFail($validated['project_manager_id']);

        ProjectRole::query()
            ->where('project_id', $project->id)
            ->findOrFail($validated['project_role_id']);

        $employee = Employee::query()->findOrFail($validated['employee_id']);

        if ($employee->user_id !== null) {
            throw ValidationException::withMessages([
                'employee_id' => 'Karyawan yang dipilih tidak valid.',
            ]);
        }

        $exists = ProjectEmployee::query()
            ->where('project_manager_id', $pmLink->id)
            ->where('employee_id', $validated['employee_id'])
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'employee_id' => 'Karyawan sudah terdaftar pada PM ini.',
            ]);
        }

        DB::transaction(function () use ($pmLink, $validated) {
            ProjectEmployee::create([
                'project_manager_id' => $pmLink->id,
                'employee_id' => $validated['employee_id'],
                'project_role_id' => $validated['project_role_id'],
            ]);
        });

        return redirect()->route('admin.projects.show', $project->id)->with('success', 'Anggota tim berhasil ditambahkan.');
    }

    public function unassignEmployee(string $projectId, string $projectEmployeeId)
    {
        $project = Project::query()->findOrFail($projectId);

        $projectEmployee = ProjectEmployee::query()
            ->whereHas('projectManager', function ($q) use ($project) {
                $q->where('project_id', $project->id);
            })
            ->findOrFail($projectEmployeeId);

        $projectEmployee->delete();

        return redirect()->route('admin.projects.show', $project->id)->with('success', 'Anggota tim berhasil dihapus.');
    }
}
