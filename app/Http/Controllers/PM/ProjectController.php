<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Models\ProjectManager;
use App\Models\ProjectEquipment;
use App\Models\ProjectMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    private function pmEmployee(): Employee
    {
        $user = Auth::user();
        $employee = $user?->employee;

        if (!$employee) {
            abort(403);
        }

        return $employee;
    }

    private function assertProjectAccess(Project $project): void
    {
        $employeeId = $this->pmEmployee()->id;

        $isPm = $project->projectManagers()->where('pm_id', $employeeId)->exists();
        if (!$isPm) {
            abort(403);
        }
    }

    public function index()
    {
        $employee = $this->pmEmployee();

        $projects = $employee->projectsAsPm()
            ->with(['client'])
            ->withCount(['materials', 'equipments'])
            ->orderByDesc('start_date')
            ->distinct()
            ->get();

        return view('pm.project.index', compact('projects'));
    }

    public function show(Project $project)
    {
        $this->assertProjectAccess($project);

        $employeeId = $this->pmEmployee()->id;
        $pmLink = ProjectManager::query()
            ->where('project_id', $project->id)
            ->where('pm_id', $employeeId)
            ->whereNull('deleted_at')
            ->first();

        if (!$pmLink) {
            abort(403);
        }

        $teamMembers = ProjectEmployee::query()
            ->where('project_manager_id', $pmLink->id)
            ->whereNull('deleted_at')
            ->with('employee')
            ->get()
            ->map(fn ($pe) => $pe->employee)
            ->filter()
            ->unique('id')
            ->values();

        $project->load([
            'client',
            'materials',
            'equipments',
            'tasks' => function ($q) use ($pmLink) {
                $q->where('project_manager_id', $pmLink->id)
                    ->with(['assignments.employee', 'subtasks']);
            },
        ]);

        return view('pm.project.show', compact('project', 'pmLink', 'teamMembers'));
    }

    public function storeMaterial(Request $request, Project $project)
    {
        $this->assertProjectAccess($project);

        $validated = $request->validate([
            'material_name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($validated, $project) {
            ProjectMaterial::create([
                'project_id' => $project->id,
                'material_name' => $validated['material_name'],
                'unit' => $validated['unit'],
            ]);
        });

        return back()->with('success', 'Material berhasil ditambahkan.');
    }

    public function updateMaterial(Request $request, Project $project, ProjectMaterial $material)
    {
        $this->assertProjectAccess($project);

        if ($material->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'material_name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($validated, $material) {
            $material->update([
                'material_name' => $validated['material_name'],
                'unit' => $validated['unit'],
            ]);
        });

        return back()->with('success', 'Material berhasil diperbarui.');
    }

    public function destroyMaterial(Project $project, ProjectMaterial $material)
    {
        $this->assertProjectAccess($project);

        if ($material->project_id !== $project->id) {
            abort(404);
        }

        DB::transaction(function () use ($material) {
            $material->delete();
        });

        return back()->with('success', 'Material berhasil dihapus.');
    }

    public function storeEquipment(Request $request, Project $project)
    {
        $this->assertProjectAccess($project);

        $validated = $request->validate([
            'equipment_name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($validated, $project) {
            ProjectEquipment::create([
                'project_id' => $project->id,
                'equipment_name' => $validated['equipment_name'],
                'unit' => $validated['unit'],
            ]);
        });

        return back()->with('success', 'Peralatan berhasil ditambahkan.');
    }

    public function updateEquipment(Request $request, Project $project, ProjectEquipment $equipment)
    {
        $this->assertProjectAccess($project);

        if ($equipment->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'equipment_name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($validated, $equipment) {
            $equipment->update([
                'equipment_name' => $validated['equipment_name'],
                'unit' => $validated['unit'],
            ]);
        });

        return back()->with('success', 'Peralatan berhasil diperbarui.');
    }

    public function destroyEquipment(Project $project, ProjectEquipment $equipment)
    {
        $this->assertProjectAccess($project);

        if ($equipment->project_id !== $project->id) {
            abort(404);
        }

        DB::transaction(function () use ($equipment) {
            $equipment->delete();
        });

        return back()->with('success', 'Peralatan berhasil dihapus.');
    }
}
