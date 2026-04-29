<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    public function index()
    {
        $projects = Project::query()
            ->orderBy('project_name')
            ->get(['id', 'project_name']);

        return view('admin.calendar.index', compact('projects'));
    }

    public function downloadDailyReportsByDate(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
        ]);

        $startDate = Carbon::parse($validated['start_date'])->toDateString();
        $endDate = Carbon::parse($validated['end_date'])->toDateString();
        $projectId = $validated['project_id'] ?? null;

        $reportsQuery = DailyReport::query()
            ->whereNull('deleted_at')
            ->whereDate('report_date', '>=', $startDate)
            ->whereDate('report_date', '<=', $endDate)
            ->with([
                'projectManager.project.client',
                'supervisor',
                'executor',
                'works.task',
                'materials.projectMaterial',
                'equipments.projectEquipment',
            ])
            ->whereHas('projectManager', function ($q) use ($projectId) {
                $q->whereNull('deleted_at');

                if ($projectId) {
                    $q->where('project_id', $projectId);
                }
            })
            ->orderBy('report_date')
            ->orderBy('created_at');

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
        $projectId = $request->query('project_id');

        $start = $request->query('start');
        $end = $request->query('end');

        $startDate = $start ? Carbon::parse($start)->toDateString() : now()->startOfMonth()->toDateString();
        $endDate = $end ? Carbon::parse($end)->toDateString() : now()->endOfMonth()->addDay()->toDateString();

        $projectsQuery = Project::query()
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

        $reportsQuery = DailyReport::query()
            ->select([
                'daily_reports.id',
                'daily_reports.report_date',
                'daily_reports.weather_condition',
                'daily_reports.weather_time',
                'daily_reports.weather_notes',
                'daily_reports.supervisor_name',
                'daily_reports.executor_name',
                'p.id as project_id',
                'p.project_name',
            ])
            ->join('project_managers as pm', function ($join) {
                $join->on('pm.id', '=', 'daily_reports.project_manager_id')
                    ->whereNull('pm.deleted_at');
            })
            ->join('projects as p', function ($join) {
                $join->on('p.id', '=', 'pm.project_id')
                    ->whereNull('p.deleted_at');
            })
            ->whereNull('daily_reports.deleted_at')
            ->whereDate('daily_reports.report_date', '>=', $startDate)
            ->whereDate('daily_reports.report_date', '<', $endDate);

        if ($projectId) {
            $reportsQuery->where('p.id', $projectId);
        }

        $reportEvents = $reportsQuery
            ->orderBy('daily_reports.report_date')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => 'report-' . $r->id,
                    'title' => 'Daily Report - ' . $r->project_name,
                    'start' => $r->report_date,
                    'allDay' => true,
                    'backgroundColor' => '#50CD89',
                    'borderColor' => '#50CD89',
                    'extendedProps' => [
                        'type' => 'report',
                        'model_id' => $r->id,
                        'project_id' => $r->project_id,
                        'project_name' => $r->project_name,
                        'report_date' => $r->report_date,
                        'weather_condition' => $r->weather_condition,
                        'weather_time' => $r->weather_time,
                        'weather_notes' => $r->weather_notes,
                        'supervisor_name' => $r->supervisor_name,
                        'executor_name' => $r->executor_name,
                    ],
                ];
            })
            ->values()
            ->all();

        return response()->json(array_merge($projectEvents, $reportEvents));
    }
}
