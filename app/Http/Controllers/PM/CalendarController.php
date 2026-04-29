<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function downloadDailyReportsByDate(Request $request)
    {
        $employeeId = $this->pmEmployee()->id;

        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
        ]);

        $startDate = Carbon::parse($validated['start_date'])->toDateString();
        $endDate = Carbon::parse($validated['end_date'])->toDateString();
        $projectId = $validated['project_id'] ?? null;

        $reportsQuery = DailyReport::query()
            ->whereNull('daily_reports.deleted_at')
            ->whereDate('daily_reports.report_date', '>=', $startDate)
            ->whereDate('daily_reports.report_date', '<=', $endDate)
            ->whereHas('projectManager', function ($q) use ($employeeId, $projectId) {
                $q->where('pm_id', $employeeId)
                    ->whereNull('deleted_at');

                if ($projectId) {
                    $q->where('project_id', $projectId);
                }
            })
            ->with([
                'projectManager.project.client',
                'supervisor',
                'executor',
                'works.task',
                'materials.projectMaterial',
                'equipments.projectEquipment',
            ])
            ->orderBy('daily_reports.report_date')
            ->orderBy('daily_reports.created_at');

        $reports = $reportsQuery->get();

        if ($reports->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada Daily Report pada rentang tanggal tersebut.');
        }

        $items = $reports->map(function (DailyReport $dailyReport) {
            $project = $dailyReport->projectManager?->project;
            if (!$project) {
                return null;
            }

            $executionDays = null;
            if ($project->start_date && $project->end_date) {
                $executionDays = Carbon::parse($project->start_date)->diffInDays(Carbon::parse($project->end_date)) + 1;
            }

            $reportDate = $dailyReport->report_date;
            $pmLinkId = $dailyReport->project_manager_id;

            $manpowerByRole = DB::table('employee_task_logs as l')
                ->join('tasks as t', 't.id', '=', 'l.task_id')
                ->join('project_employees as pe', function ($join) use ($pmLinkId) {
                    $join->on('pe.employee_id', '=', 'l.employee_id')
                        ->where('pe.project_manager_id', '=', $pmLinkId)
                        ->whereNull('pe.deleted_at');
                })
                ->join('project_roles as pr', 'pr.id', '=', 'pe.project_role_id')
                ->whereNull('l.deleted_at')
                ->where('l.log_date', $reportDate)
                ->where('t.project_manager_id', $pmLinkId)
                ->groupBy('pr.role_name')
                ->select('pr.role_name', DB::raw('COUNT(DISTINCT l.employee_id) as total'))
                ->orderBy('pr.role_name')
                ->get();

            return [
                'dailyReport' => $dailyReport,
                'executionDays' => $executionDays,
                'manpowerByRole' => $manpowerByRole,
            ];
        })->filter()->values();

        $pdf = Pdf::loadView('pdf.daily_report_range', compact('items'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('daily-reports-' . $startDate . '-' . $endDate . '.pdf');
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
