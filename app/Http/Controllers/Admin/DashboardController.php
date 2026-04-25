<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $totalClients = Client::count();
        $totalEmployees = Employee::count();
        $totalActiveProjects = Project::query()->where('status', 'active')->count();

        $projects = Project::query()
            ->where('status', 'active')
            ->with(['client', 'projectManagers.pm'])
            ->withCount([
                'tasks as total_tasks' => function ($q) {
                    $q->whereNull('tasks.deleted_at');
                },
                'tasks as done_tasks' => function ($q) use ($today) {
                    $q->whereNull('tasks.deleted_at')
                        ->where(function ($qq) use ($today) {
                            $qq->whereDate('tasks.end_date', '<', $today)
                                ->orWhereExists(function ($sub) {
                                    $sub->select(DB::raw(1))
                                        ->from('report_works as rw')
                                        ->join('daily_reports as dr', 'dr.id', '=', 'rw.daily_report_id')
                                        ->whereNull('dr.deleted_at')
                                        ->whereColumn('rw.task_id', 'tasks.id');
                                });
                        });
                },
            ])
            ->orderByRaw('end_date is null')
            ->orderBy('end_date')
            ->limit(10)
            ->get();

        $projects->each(function ($project) {
            $total = (int) ($project->total_tasks ?? 0);
            $done = (int) ($project->done_tasks ?? 0);
            $percent = $total > 0 ? (int) round(($done / $total) * 100) : 0;
            $project->setAttribute('progress_percent', max(0, min(100, $percent)));
        });

        $manpowerByRole = DB::table('employee_task_logs as l')
            ->join('tasks as t', 't.id', '=', 'l.task_id')
            ->join('project_employees as pe', function ($join) {
                $join->on('pe.employee_id', '=', 'l.employee_id')
                    ->whereColumn('pe.project_manager_id', 't.project_manager_id')
                    ->whereNull('pe.deleted_at');
            })
            ->join('project_roles as pr', 'pr.id', '=', 'pe.project_role_id')
            ->whereNull('l.deleted_at')
            ->whereNull('t.deleted_at')
            ->whereDate('l.log_date', $today)
            ->groupBy('pr.role_name')
            ->select('pr.role_name', DB::raw('COUNT(DISTINCT l.employee_id) as total'))
            ->orderBy('pr.role_name')
            ->get();

        $totalManpowerToday = $manpowerByRole->sum('total');

        $manpowerChart = [
            'labels' => $manpowerByRole->pluck('role_name')->values()->all(),
            'data' => $manpowerByRole->pluck('total')->values()->all(),
        ];

        return view('admin.dashboard', compact(
            'totalClients',
            'totalEmployees',
            'totalActiveProjects',
            'projects',
            'manpowerByRole',
            'totalManpowerToday',
            'manpowerChart'
        ));
    }
}
