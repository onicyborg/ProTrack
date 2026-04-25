<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DailyReportController extends Controller
{
    public function show(DailyReport $dailyReport)
    {
        $dailyReport = DailyReport::query()
            ->where('id', $dailyReport->id)
            ->with([
                'projectManager.project.client',
                'projectManager.project.roles',
                'supervisor',
                'executor',
                'works.task',
                'materials.projectMaterial',
                'equipments.projectEquipment',
            ])
            ->firstOrFail();

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

        return view('admin.daily_report.show', compact('dailyReport', 'manpowerByRole'));
    }

    public function downloadPdf(DailyReport $dailyReport)
    {
        $dailyReport = DailyReport::query()
            ->where('id', $dailyReport->id)
            ->with([
                'projectManager.project.client',
                'supervisor',
                'executor',
                'works.task',
                'materials.projectMaterial',
                'equipments.projectEquipment',
            ])
            ->firstOrFail();

        $project = $dailyReport->projectManager?->project;
        if (!$project) {
            abort(404);
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

        $pdf = Pdf::loadView('pdf.daily_report', compact('dailyReport', 'manpowerByRole', 'executionDays'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('daily-report-' . $dailyReport->id . '.pdf');
    }
}
