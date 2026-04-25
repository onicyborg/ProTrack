<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
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

    public function index(Request $request)
    {
        $employeeId = $this->pmEmployee()->id;

        $projects = Project::query()
            ->whereHas('projectManagers', function ($q) use ($employeeId) {
                $q->where('pm_id', $employeeId)
                    ->whereNull('deleted_at');
            })
            ->orderBy('project_name')
            ->get(['id', 'project_name']);

        $selectedProjectId = $request->query('project_id');

        return view('pm.calendar.index', compact('projects', 'selectedProjectId'));
    }

    public function getEvents(Request $request)
    {
        $employeeId = $this->pmEmployee()->id;

        $projectId = $request->query('project_id');
        $start = $request->query('start');
        $end = $request->query('end');

        $startDate = $start ? Carbon::parse($start)->toDateString() : now()->startOfMonth()->toDateString();
        $endDate = $end ? Carbon::parse($end)->toDateString() : now()->endOfMonth()->addDay()->toDateString();

        $projectsQuery = Project::query()
            ->whereHas('projectManagers', function ($q) use ($employeeId) {
                $q->where('pm_id', $employeeId)
                    ->whereNull('deleted_at');
            })
            ->whereNotNull('start_date')
            ->whereDate('start_date', '<', $endDate)
            ->where(function ($q) use ($startDate) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $startDate);
            })
            ->select(['id', 'project_name', 'start_date', 'end_date']);

        if ($projectId) {
            $projectsQuery->where('id', $projectId);
        }

        $projectEvents = $projectsQuery
            ->orderBy('start_date')
            ->get()
            ->map(function ($p) {
                $eventEnd = $p->end_date ? Carbon::parse($p->end_date)->addDay()->toDateString() : Carbon::parse($p->start_date)->addDay()->toDateString();

                return [
                    'id' => 'project-' . $p->id,
                    'title' => $p->project_name,
                    'start' => $p->start_date,
                    'end' => $eventEnd,
                    'allDay' => true,
                    'backgroundColor' => '#3E97FF',
                    'borderColor' => '#3E97FF',
                    'extendedProps' => [
                        'type' => 'project',
                        'model_id' => $p->id,
                    ],
                ];
            })
            ->values()
            ->all();

        $tasksQuery = Task::query()
            ->whereNull('tasks.deleted_at')
            ->whereNotNull('tasks.start_date')
            ->whereDate('tasks.start_date', '<', $endDate)
            ->where(function ($q) use ($startDate) {
                $q->whereNull('tasks.end_date')
                    ->orWhereDate('tasks.end_date', '>=', $startDate);
            })
            ->with(['projectManager.project'])
            ->whereHas('projectManager', function ($q) use ($employeeId, $projectId) {
                $q->where('pm_id', $employeeId)
                    ->whereNull('deleted_at');

                if ($projectId) {
                    $q->where('project_id', $projectId);
                }
            })
            ->select(['id', 'project_manager_id', 'task_name', 'start_date', 'end_date']);

        $rangeStart = Carbon::parse($startDate)->startOfDay();
        $rangeEndInclusive = Carbon::parse($endDate)->subDay()->startOfDay();

        $taskEvents = [];
        foreach ($tasksQuery->orderBy('tasks.start_date')->get() as $t) {
            $taskStart = Carbon::parse($t->start_date)->startOfDay();
            $taskEnd = $t->end_date ? Carbon::parse($t->end_date)->startOfDay() : Carbon::parse($t->start_date)->startOfDay();

            $loopStart = $taskStart->greaterThan($rangeStart) ? $taskStart->copy() : $rangeStart->copy();
            $loopEnd = $taskEnd->lessThan($rangeEndInclusive) ? $taskEnd->copy() : $rangeEndInclusive->copy();

            if ($loopStart->greaterThan($loopEnd)) {
                continue;
            }

            foreach (CarbonPeriod::create($loopStart, $loopEnd) as $d) {
                $dateStr = $d->toDateString();
                $taskEvents[] = [
                    'id' => 'task-' . $t->id . '-' . $dateStr,
                    'groupId' => 'task-' . $t->id,
                    'title' => $t->task_name,
                    'start' => $dateStr,
                    'allDay' => true,
                    'backgroundColor' => '#FFC700',
                    'borderColor' => '#FFC700',
                    'textColor' => '#111111',
                    'extendedProps' => [
                        'type' => 'wbs',
                        'task_id' => $t->id,
                        'date' => $dateStr,
                        'project_id' => $t->projectManager?->project_id,
                        'project_name' => $t->projectManager?->project?->project_name,
                    ],
                ];
            }
        }

        return response()->json(array_merge($projectEvents, $taskEvents));
    }

    public function checkReport(Request $request)
    {
        $employeeId = $this->pmEmployee()->id;

        $validated = $request->validate([
            'task_id' => ['required', 'uuid', 'exists:tasks,id'],
            'date' => ['required', 'date'],
        ]);

        $task = Task::query()
            ->where('id', $validated['task_id'])
            ->whereNull('deleted_at')
            ->whereHas('projectManager', function ($q) use ($employeeId) {
                $q->where('pm_id', $employeeId)
                    ->whereNull('deleted_at');
            })
            ->with(['projectManager.project'])
            ->firstOrFail();

        $pmLinkId = $task->project_manager_id;
        $projectId = $task->projectManager?->project_id;

        $existing = DailyReport::query()
            ->where('project_manager_id', $pmLinkId)
            ->whereNull('deleted_at')
            ->whereDate('report_date', $validated['date'])
            ->whereHas('works', function ($q) use ($task) {
                $q->where('task_id', $task->id);
            })
            ->orderByDesc('created_at')
            ->first();

        if ($existing) {
            return response()->json([
                'redirect_url' => route('pm.daily-reports.show', $existing->id),
                'status' => 'exists',
            ]);
        }

        return response()->json([
            'redirect_url' => route('pm.daily-reports.create', [
                'project_id' => $projectId,
                'date' => Carbon::parse($validated['date'])->toDateString(),
                'task_id' => $task->id,
            ]),
            'status' => 'create',
        ]);
    }
}
