<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Models\ProjectManager;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskSubtask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectTaskController extends Controller
{
    private function taskRules(Project $project): array
    {
        $rules = [
            'task_name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];

        if ($project->start_date) {
            $rules['start_date'][] = 'after_or_equal:' . $project->start_date;
            $rules['end_date'][] = 'after_or_equal:' . $project->start_date;
        }

        if ($project->end_date) {
            $rules['start_date'][] = 'before_or_equal:' . $project->end_date;
            $rules['end_date'][] = 'before_or_equal:' . $project->end_date;
        }

        return $rules;
    }

    private function pmEmployee(): Employee
    {
        $user = Auth::user();
        $employee = $user?->employee;

        if (!$employee) {
            abort(403);
        }

        return $employee;
    }

    private function pmLink(Project $project): ProjectManager
    {
        $employeeId = $this->pmEmployee()->id;

        $link = ProjectManager::query()
            ->where('project_id', $project->id)
            ->where('pm_id', $employeeId)
            ->whereNull('deleted_at')
            ->first();

        if (!$link) {
            abort(403);
        }

        return $link;
    }

    private function assertTaskBelongsToPm(ProjectManager $pmLink, Task $task): void
    {
        if ($task->project_manager_id !== $pmLink->id) {
            abort(404);
        }
    }

    public function store(Request $request, Project $project)
    {
        $pmLink = $this->pmLink($project);

        $validated = $request->validate($this->taskRules($project));

        DB::transaction(function () use ($validated, $pmLink) {
            Task::create([
                'project_manager_id' => $pmLink->id,
                'task_name' => $validated['task_name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ]);
        });

        return back()->with('success', 'Task berhasil ditambahkan.');
    }

    public function update(Request $request, Project $project, Task $task)
    {
        $pmLink = $this->pmLink($project);
        $this->assertTaskBelongsToPm($pmLink, $task);

        $validated = $request->validate($this->taskRules($project));

        DB::transaction(function () use ($validated, $task) {
            $task->update([
                'task_name' => $validated['task_name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ]);
        });

        return back()->with('success', 'Task berhasil diperbarui.');
    }

    public function destroy(Project $project, Task $task)
    {
        $pmLink = $this->pmLink($project);
        $this->assertTaskBelongsToPm($pmLink, $task);

        DB::transaction(function () use ($task) {
            $task->delete();
        });

        return back()->with('success', 'Task berhasil dihapus.');
    }

    public function syncAssignments(Request $request, Project $project, Task $task)
    {
        $pmLink = $this->pmLink($project);
        $this->assertTaskBelongsToPm($pmLink, $task);

        $validated = $request->validate([
            'employee_ids' => ['nullable', 'array'],
            'employee_ids.*' => ['required', 'uuid', 'exists:employees,id'],
        ]);

        $employeeIds = collect($validated['employee_ids'] ?? [])->filter()->values()->all();

        $allowedEmployeeIds = ProjectEmployee::query()
            ->where('project_manager_id', $pmLink->id)
            ->whereNull('deleted_at')
            ->pluck('employee_id')
            ->unique()
            ->values()
            ->all();

        $diff = array_diff($employeeIds, $allowedEmployeeIds);
        if (count($diff) > 0) {
            abort(403);
        }

        DB::transaction(function () use ($task, $employeeIds) {
            if (count($employeeIds) === 0) {
                TaskAssignment::query()->where('task_id', $task->id)->delete();
                return;
            }

            TaskAssignment::query()
                ->where('task_id', $task->id)
                ->whereNotIn('employee_id', $employeeIds)
                ->delete();

            $existing = TaskAssignment::query()
                ->where('task_id', $task->id)
                ->pluck('employee_id')
                ->all();

            foreach ($employeeIds as $employeeId) {
                if (in_array($employeeId, $existing, true)) {
                    continue;
                }

                TaskAssignment::create([
                    'task_id' => $task->id,
                    'employee_id' => $employeeId,
                ]);
            }
        });

        return back()->with('success', 'Anggota task berhasil diperbarui.');
    }

    public function storeSubtask(Request $request, Project $project, Task $task)
    {
        $pmLink = $this->pmLink($project);
        $this->assertTaskBelongsToPm($pmLink, $task);

        $validated = $request->validate([
            'subtask_name' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $task) {
            TaskSubtask::create([
                'task_id' => $task->id,
                'subtask_name' => $validated['subtask_name'],
            ]);
        });

        if ($request->expectsJson()) {
            $subtasks = TaskSubtask::query()
                ->where('task_id', $task->id)
                ->orderBy('created_at')
                ->get()
                ->map(fn ($s) => ['id' => $s->id, 'name' => $s->subtask_name])
                ->values();

            return response()->json([
                'message' => 'Sub task berhasil ditambahkan.',
                'subtasks' => $subtasks,
            ]);
        }

        return back()->with('success', 'Sub task berhasil ditambahkan.');
    }

    public function destroySubtask(Request $request, Project $project, Task $task, TaskSubtask $subtask)
    {
        $pmLink = $this->pmLink($project);
        $this->assertTaskBelongsToPm($pmLink, $task);

        if ($subtask->task_id !== $task->id) {
            abort(404);
        }

        DB::transaction(function () use ($subtask) {
            $subtask->delete();
        });

        if ($request->expectsJson()) {
            $subtasks = TaskSubtask::query()
                ->where('task_id', $task->id)
                ->orderBy('created_at')
                ->get()
                ->map(fn ($s) => ['id' => $s->id, 'name' => $s->subtask_name])
                ->values();

            return response()->json([
                'message' => 'Sub task berhasil dihapus.',
                'subtasks' => $subtasks,
            ]);
        }

        return back()->with('success', 'Sub task berhasil dihapus.');
    }
}
