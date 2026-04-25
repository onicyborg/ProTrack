<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Models\ProjectManager;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
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

    public function index()
    {
        $employeeId = $this->pmEmployee()->id;
        $today = now()->toDateString();

        $pmLinks = ProjectManager::query()
            ->where('pm_id', $employeeId)
            ->whereNull('deleted_at')
            ->with(['project.client'])
            ->get();

        $pmLinkIds = $pmLinks->pluck('id')->values()->all();
        $projectIds = $pmLinks->pluck('project_id')->values()->all();

        $activeProjectsCount = Project::query()
            ->whereIn('id', $projectIds)
            ->where('status', 'active')
            ->count();

        $totalTeamMembers = ProjectEmployee::query()
            ->whereIn('project_manager_id', $pmLinkIds)
            ->whereNull('deleted_at')
            ->whereHas('employee', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->distinct('employee_id')
            ->count('employee_id');

        $runningTasksCount = Task::query()
            ->whereIn('project_manager_id', $pmLinkIds)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhereDate('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
            })
            ->count();

        $recentReports = DailyReport::query()
            ->whereIn('project_manager_id', $pmLinkIds)
            ->whereNull('deleted_at')
            ->with(['projectManager.project', 'supervisor', 'executor'])
            ->orderByDesc('report_date')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $hasTodayReport = DailyReport::query()
            ->whereIn('project_manager_id', $pmLinkIds)
            ->whereNull('deleted_at')
            ->whereDate('report_date', $today)
            ->exists();

        $shouldWarnNoReport = !now()->isWeekend() && !$hasTodayReport;

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
            ->whereIn('t.project_manager_id', $pmLinkIds)
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

        return view('pm.dashboard', compact(
            'activeProjectsCount',
            'totalTeamMembers',
            'runningTasksCount',
            'recentReports',
            'shouldWarnNoReport',
            'pmLinks',
            'manpowerByRole',
            'totalManpowerToday',
            'manpowerChart'
        ));
    }
}
