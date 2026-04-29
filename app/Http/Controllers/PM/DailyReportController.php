<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\Employee;
use App\Models\EmployeeTaskLog;
use App\Models\Project;
use App\Models\ProjectEquipment;
use App\Models\ProjectEmployee;
use App\Models\ProjectManager;
use App\Models\ProjectMaterial;
use App\Models\ReportEquipment;
use App\Models\ReportMaterial;
use App\Models\ReportWork;
use App\Models\Task;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DailyReportController extends Controller
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

    private function assertReportBelongsToPm(ProjectManager $pmLink, DailyReport $dailyReport): void
    {
        if ($dailyReport->project_manager_id !== $pmLink->id) {
            abort(404);
        }
    }

    private function headerRules(Project $project, ProjectManager $pmLink): array
    {
        $rules = [
            'report_date' => ['required', 'date'],
            'kegiatan' => ['nullable', 'string', 'max:255'],
            'rincian_kegiatan' => ['nullable', 'string', 'max:255'],
            'lokasi_kegiatan' => ['nullable', 'string', 'max:255'],
            'weather_condition' => ['required', Rule::in(['Cerah', 'Hujan', 'Mendung'])],
            'weather_time' => ['nullable', 'string', 'max:50'],
            'weather_notes' => ['nullable', 'string'],
            'supervisor_id' => [
                'nullable',
                'uuid',
                Rule::exists('employees', 'id')->where(function ($q) {
                    $q->whereNull('deleted_at');
                }),
                Rule::exists('project_employees', 'employee_id')->where(function ($q) use ($pmLink) {
                    $q->where('project_manager_id', $pmLink->id)
                        ->whereNull('deleted_at');
                }),
            ],
            'executor_id' => [
                'nullable',
                'uuid',
                Rule::exists('employees', 'id')->where(function ($q) {
                    $q->whereNull('deleted_at');
                }),
                Rule::exists('project_employees', 'employee_id')->where(function ($q) use ($pmLink) {
                    $q->where('project_manager_id', $pmLink->id)
                        ->whereNull('deleted_at');
                }),
            ],
        ];

        if ($project->start_date) {
            $rules['report_date'][] = 'after_or_equal:' . $project->start_date;
        }
        if ($project->end_date) {
            $rules['report_date'][] = 'before_or_equal:' . $project->end_date;
        }

        return $rules;
    }

    private function detailRules(Project $project, ProjectManager $pmLink, ?string $reportDate = null): array
    {
        $taskIdRule = Rule::exists('tasks', 'id')
            ->where(function ($q) use ($pmLink, $reportDate) {
                $q->where('project_manager_id', $pmLink->id)
                    ->whereNull('deleted_at');

                if ($reportDate) {
                    $q->where(function ($qq) use ($reportDate) {
                        $qq->whereNull('start_date')->orWhereDate('start_date', '<=', $reportDate);
                    })->where(function ($qq) use ($reportDate) {
                        $qq->whereNull('end_date')->orWhereDate('end_date', '>=', $reportDate);
                    });
                }
            });

        return [
            'works' => ['required', 'array', 'min:1'],
            'works.*.task_id' => [
                'required',
                'uuid',
                $taskIdRule,
            ],
            'works.*.volume' => ['required', 'numeric', 'min:0'],

            'materials' => ['nullable', 'array'],
            'materials.*.project_material_id' => [
                'required',
                'uuid',
                Rule::exists('project_materials', 'id')
                    ->where(function ($q) use ($project) {
                        $q->where('project_id', $project->id)
                            ->whereNull('deleted_at');
                    }),
            ],
            'materials.*.volume' => ['required', 'numeric', 'min:0'],
            'materials.*.notes' => ['nullable', 'string'],

            'equipments' => ['nullable', 'array'],
            'equipments.*.project_equipment_id' => [
                'required',
                'uuid',
                Rule::exists('project_equipments', 'id')
                    ->where(function ($q) use ($project) {
                        $q->where('project_id', $project->id)
                            ->whereNull('deleted_at');
                    }),
            ],
            'equipments.*.volume' => ['required', 'numeric', 'min:0'],
        ];
    }

    private function teamRules(ProjectManager $pmLink): array
    {
        return [
            'team_present' => ['nullable', 'array'],
            'team_present.*' => [
                'uuid',
                Rule::exists('employees', 'id')->where(function ($q) {
                    $q->whereNull('deleted_at');
                }),
                Rule::exists('project_employees', 'employee_id')->where(function ($q) use ($pmLink) {
                    $q->where('project_manager_id', $pmLink->id)
                        ->whereNull('deleted_at');
                }),
            ],
            'team_activity' => ['nullable', 'array'],
            'team_activity.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function index(Request $request)
    {
        $employeeId = $this->pmEmployee()->id;

        $pmLinks = ProjectManager::query()
            ->where('pm_id', $employeeId)
            ->whereNull('deleted_at')
            ->with(['project.client'])
            ->get();

        $selectedProjectId = $request->query('project_id');
        $selectedProjectId = $selectedProjectId ?: null;

        $reportsQuery = DailyReport::query()
            ->whereIn('project_manager_id', $pmLinks->pluck('id')->all())
            ->with(['projectManager.project.client', 'supervisor'])
            ->orderByDesc('report_date');

        if ($selectedProjectId) {
            $pmLink = $pmLinks->firstWhere('project_id', $selectedProjectId);
            if ($pmLink) {
                $reportsQuery->where('project_manager_id', $pmLink->id);
            }
        }

        $reports = $reportsQuery->get();

        return view('pm.daily_report.index', compact('pmLinks', 'selectedProjectId', 'reports'));
    }

    public function create(Request $request)
    {
        $projectId = $request->query('project_id');
        if (!$projectId) {
            return redirect()->route('pm.daily-reports.index')->with('error', 'Pilih proyek terlebih dahulu.');
        }

        $prefillReportDate = null;
        $prefillTaskId = null;

        $project = Project::query()->findOrFail($projectId);
        $pmLink = $this->pmLink($project);

        $dateParam = $request->query('date');
        if ($dateParam) {
            try {
                $prefillReportDate = Carbon::parse($dateParam)->toDateString();
            } catch (\Throwable $e) {
                $prefillReportDate = null;
            }
        }

        $taskParam = $request->query('task_id');
        if ($taskParam) {
            $exists = Task::query()
                ->where('id', $taskParam)
                ->where('project_manager_id', $pmLink->id)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                $prefillTaskId = $taskParam;
            }
        }

        $tasks = Task::query()
            ->where('project_manager_id', $pmLink->id)
            ->orderBy('task_name')
            ->get();

        $materials = ProjectMaterial::query()
            ->where('project_id', $project->id)
            ->whereNull('deleted_at')
            ->orderBy('material_name')
            ->get();

        $equipments = ProjectEquipment::query()
            ->where('project_id', $project->id)
            ->whereNull('deleted_at')
            ->orderBy('equipment_name')
            ->get();

        $teamEmployees = ProjectEmployee::query()
            ->where('project_manager_id', $pmLink->id)
            ->whereNull('deleted_at')
            ->whereHas('employee', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->with(['employee', 'projectRole'])
            ->get()
            ->sortBy(function ($row) {
                return $row->employee?->employee_name ?? '';
            })
            ->values();

        $teamPresentIds = [];
        $teamActivityById = [];

        return view('pm.daily_report.create', compact('project', 'pmLink', 'tasks', 'materials', 'equipments', 'teamEmployees', 'teamPresentIds', 'teamActivityById', 'prefillReportDate', 'prefillTaskId'));
    }

    public function store(Request $request)
    {
        $validatedProject = $request->validate([
            'project_id' => ['required', 'uuid', 'exists:projects,id'],
        ]);

        $project = Project::query()->findOrFail($validatedProject['project_id']);
        $pmLink = $this->pmLink($project);

        $validatedHeader = $request->validate($this->headerRules($project, $pmLink));
        $validatedDetail = $request->validate($this->detailRules($project, $pmLink, $validatedHeader['report_date']));
        $validatedTeam = $request->validate($this->teamRules($pmLink));

        $validated = array_merge($validatedHeader, $validatedDetail, $validatedTeam);

        $teamPresentIds = collect($validated['team_present'] ?? [])->filter()->unique()->values();
        $teamActivityById = is_array(($validated['team_activity'] ?? null)) ? $validated['team_activity'] : [];
        $attendanceTaskId = collect($validated['works'] ?? [])->first()['task_id'] ?? null;

        $selectedEmployeeIds = collect([
            $validated['supervisor_id'] ?? null,
            $validated['executor_id'] ?? null,
        ])->filter()->unique()->values();

        $employeeNameById = Employee::query()
            ->whereIn('id', $selectedEmployeeIds)
            ->whereNull('deleted_at')
            ->pluck('employee_name', 'id');

        $dailyReport = DB::transaction(function () use ($validated, $pmLink, $employeeNameById, $teamPresentIds, $teamActivityById, $attendanceTaskId) {
            $dailyReport = DailyReport::create([
                'project_manager_id' => $pmLink->id,
                'report_date' => $validated['report_date'],
                'kegiatan' => $validated['kegiatan'] ?? null,
                'rincian_kegiatan' => $validated['rincian_kegiatan'] ?? null,
                'lokasi_kegiatan' => $validated['lokasi_kegiatan'] ?? null,
                'weather_condition' => $validated['weather_condition'],
                'weather_time' => $validated['weather_time'] ?? null,
                'weather_notes' => $validated['weather_notes'] ?? null,
                'supervisor_id' => $validated['supervisor_id'] ?? null,
                'executor_id' => $validated['executor_id'] ?? null,
                'supervisor_name' => ($validated['supervisor_id'] ?? null) ? ($employeeNameById[$validated['supervisor_id']] ?? null) : null,
                'executor_name' => ($validated['executor_id'] ?? null) ? ($employeeNameById[$validated['executor_id']] ?? null) : null,
            ]);

            foreach (($validated['works'] ?? []) as $row) {
                ReportWork::create([
                    'daily_report_id' => $dailyReport->id,
                    'task_id' => $row['task_id'],
                    'volume' => $row['volume'],
                ]);
            }

            foreach (($validated['materials'] ?? []) as $row) {
                ReportMaterial::create([
                    'daily_report_id' => $dailyReport->id,
                    'project_material_id' => $row['project_material_id'],
                    'volume' => $row['volume'],
                    'notes' => $row['notes'] ?? null,
                ]);
            }

            foreach (($validated['equipments'] ?? []) as $row) {
                ReportEquipment::create([
                    'daily_report_id' => $dailyReport->id,
                    'project_equipment_id' => $row['project_equipment_id'],
                    'volume' => $row['volume'],
                ]);
            }

            foreach (($teamPresentIds ?? []) as $employeeId) {
                EmployeeTaskLog::create([
                    'task_id' => $attendanceTaskId,
                    'employee_id' => $employeeId,
                    'log_date' => $validated['report_date'],
                    'notes' => $teamActivityById[$employeeId] ?? null,
                ]);
            }
            return $dailyReport;
        });

        return redirect()->route('pm.daily-reports.show', $dailyReport->id)->with('success', 'Laporan harian berhasil disimpan.');
    }

    public function show(DailyReport $dailyReport)
    {
        $employeeId = $this->pmEmployee()->id;

        $dailyReport = DailyReport::query()
            ->where('id', $dailyReport->id)
            ->whereHas('projectManager', function ($q) use ($employeeId) {
                $q->where('pm_id', $employeeId)->whereNull('deleted_at');
            })
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

        return view('pm.daily_report.show', compact('dailyReport', 'manpowerByRole'));
    }

    public function downloadPdf(DailyReport $dailyReport)
    {
        $employeeId = $this->pmEmployee()->id;

        $dailyReport = DailyReport::query()
            ->where('id', $dailyReport->id)
            ->whereHas('projectManager', function ($q) use ($employeeId) {
                $q->where('pm_id', $employeeId)->whereNull('deleted_at');
            })
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

    public function tasksByDate(Request $request)
    {
        $validated = $request->validate([
            'project_id' => ['required', 'uuid', 'exists:projects,id'],
            'date' => ['required', 'date'],
        ]);

        $project = Project::query()->findOrFail($validated['project_id']);
        $pmLink = $this->pmLink($project);
        $date = Carbon::parse($validated['date'])->toDateString();

        $tasks = Task::query()
            ->where('project_manager_id', $pmLink->id)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($date) {
                $q->whereNull('start_date')->orWhereDate('start_date', '<=', $date);
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $date);
            })
            ->orderBy('task_name')
            ->get(['id', 'task_name']);

        return response()->json(
            $tasks->map(function ($t) {
                return ['id' => $t->id, 'text' => $t->task_name];
            })->values()
        );
    }

    public function edit(DailyReport $dailyReport)
    {
        $employeeId = $this->pmEmployee()->id;

        $dailyReport = DailyReport::query()
            ->where('id', $dailyReport->id)
            ->whereHas('projectManager', function ($q) use ($employeeId) {
                $q->where('pm_id', $employeeId)->whereNull('deleted_at');
            })
            ->with([
                'projectManager.project',
                'supervisor',
                'executor',
                'works',
                'materials',
                'equipments',
            ])
            ->firstOrFail();

        $project = $dailyReport->projectManager->project;
        $pmLink = $this->pmLink($project);
        $this->assertReportBelongsToPm($pmLink, $dailyReport);

        $tasks = Task::query()
            ->where('project_manager_id', $pmLink->id)
            ->orderBy('task_name')
            ->get();

        $materials = ProjectMaterial::query()
            ->where('project_id', $project->id)
            ->whereNull('deleted_at')
            ->orderBy('material_name')
            ->get();

        $equipments = ProjectEquipment::query()
            ->where('project_id', $project->id)
            ->whereNull('deleted_at')
            ->orderBy('equipment_name')
            ->get();

        $teamEmployees = ProjectEmployee::query()
            ->where('project_manager_id', $pmLink->id)
            ->whereNull('deleted_at')
            ->whereHas('employee', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->with(['employee', 'projectRole'])
            ->get()
            ->sortBy(function ($row) {
                return $row->employee?->employee_name ?? '';
            })
            ->values();

        $teamEmployeeIds = $teamEmployees->pluck('employee_id')->all();
        $attendanceTaskIds = $dailyReport->works->pluck('task_id')->unique()->values()->all();
        if (count($attendanceTaskIds) === 0) {
            $attendanceTaskIds = $tasks->pluck('id')->all();
        }

        $teamLogs = EmployeeTaskLog::query()
            ->where('log_date', $dailyReport->report_date)
            ->whereIn('employee_id', $teamEmployeeIds)
            ->whereIn('task_id', $attendanceTaskIds)
            ->get(['employee_id', 'notes']);

        $teamPresentIds = $teamLogs->pluck('employee_id')->unique()->values()->all();
        $teamActivityById = $teamLogs->mapWithKeys(function ($row) {
            return [$row->employee_id => $row->notes];
        })->all();

        return view('pm.daily_report.edit', compact('dailyReport', 'project', 'pmLink', 'tasks', 'materials', 'equipments', 'teamEmployees', 'teamPresentIds', 'teamActivityById'));
    }

    public function update(Request $request, DailyReport $dailyReport)
    {
        $employeeId = $this->pmEmployee()->id;

        $dailyReport = DailyReport::query()
            ->where('id', $dailyReport->id)
            ->whereHas('projectManager', function ($q) use ($employeeId) {
                $q->where('pm_id', $employeeId)->whereNull('deleted_at');
            })
            ->with(['projectManager.project'])
            ->firstOrFail();

        $project = $dailyReport->projectManager->project;
        $pmLink = $this->pmLink($project);
        $this->assertReportBelongsToPm($pmLink, $dailyReport);

        $validatedHeader = $request->validate($this->headerRules($project, $pmLink));
        $validatedDetail = $request->validate($this->detailRules($project, $pmLink, $validatedHeader['report_date']));
        $validatedTeam = $request->validate($this->teamRules($pmLink));

        $validated = array_merge($validatedHeader, $validatedDetail, $validatedTeam);

        $teamPresentIds = collect($validated['team_present'] ?? [])->filter()->unique()->values();
        $teamActivityById = is_array(($validated['team_activity'] ?? null)) ? $validated['team_activity'] : [];
        $attendanceTaskId = collect($validated['works'] ?? [])->first()['task_id'] ?? null;

        $teamEmployeeIds = ProjectEmployee::query()
            ->where('project_manager_id', $pmLink->id)
            ->whereNull('deleted_at')
            ->pluck('employee_id')
            ->all();

        $pmTaskIds = Task::query()
            ->where('project_manager_id', $pmLink->id)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->all();

        $oldReportDate = $dailyReport->report_date;

        $selectedEmployeeIds = collect([
            $validated['supervisor_id'] ?? null,
            $validated['executor_id'] ?? null,
        ])->filter()->unique()->values();

        $employeeNameById = Employee::query()
            ->whereIn('id', $selectedEmployeeIds)
            ->whereNull('deleted_at')
            ->pluck('employee_name', 'id');

        DB::transaction(function () use ($validated, $dailyReport, $employeeNameById, $teamPresentIds, $teamActivityById, $attendanceTaskId, $teamEmployeeIds, $pmTaskIds, $oldReportDate) {
            $dailyReport->update([
                'report_date' => $validated['report_date'],
                'kegiatan' => $validated['kegiatan'] ?? null,
                'rincian_kegiatan' => $validated['rincian_kegiatan'] ?? null,
                'lokasi_kegiatan' => $validated['lokasi_kegiatan'] ?? null,
                'weather_condition' => $validated['weather_condition'],
                'weather_time' => $validated['weather_time'] ?? null,
                'weather_notes' => $validated['weather_notes'] ?? null,
                'supervisor_id' => $validated['supervisor_id'] ?? null,
                'executor_id' => $validated['executor_id'] ?? null,
                'supervisor_name' => ($validated['supervisor_id'] ?? null) ? ($employeeNameById[$validated['supervisor_id']] ?? null) : null,
                'executor_name' => ($validated['executor_id'] ?? null) ? ($employeeNameById[$validated['executor_id']] ?? null) : null,
            ]);

            $dailyReport->works()->delete();
            $dailyReport->materials()->delete();
            $dailyReport->equipments()->delete();

            foreach (($validated['works'] ?? []) as $row) {
                ReportWork::create([
                    'daily_report_id' => $dailyReport->id,
                    'task_id' => $row['task_id'],
                    'volume' => $row['volume'],
                ]);
            }

            foreach (($validated['materials'] ?? []) as $row) {
                ReportMaterial::create([
                    'daily_report_id' => $dailyReport->id,
                    'project_material_id' => $row['project_material_id'],
                    'volume' => $row['volume'],
                    'notes' => $row['notes'] ?? null,
                ]);
            }

            foreach (($validated['equipments'] ?? []) as $row) {
                ReportEquipment::create([
                    'daily_report_id' => $dailyReport->id,
                    'project_equipment_id' => $row['project_equipment_id'],
                    'volume' => $row['volume'],
                ]);
            }

            EmployeeTaskLog::query()
                ->where('log_date', $oldReportDate)
                ->whereIn('employee_id', $teamEmployeeIds)
                ->whereIn('task_id', $pmTaskIds)
                ->delete();

            if ($oldReportDate !== $validated['report_date']) {
                EmployeeTaskLog::query()
                    ->where('log_date', $validated['report_date'])
                    ->whereIn('employee_id', $teamEmployeeIds)
                    ->whereIn('task_id', $pmTaskIds)
                    ->delete();
            }

            foreach (($teamPresentIds ?? []) as $employeeId) {
                EmployeeTaskLog::create([
                    'task_id' => $attendanceTaskId,
                    'employee_id' => $employeeId,
                    'log_date' => $validated['report_date'],
                    'notes' => $teamActivityById[$employeeId] ?? null,
                ]);
            }
        });

        return redirect()->route('pm.daily-reports.show', $dailyReport->id)->with('success', 'Laporan harian berhasil diperbarui.');
    }

    public function destroy(DailyReport $dailyReport)
    {
        $employeeId = $this->pmEmployee()->id;

        $dailyReport = DailyReport::query()
            ->where('id', $dailyReport->id)
            ->whereHas('projectManager', function ($q) use ($employeeId) {
                $q->where('pm_id', $employeeId)->whereNull('deleted_at');
            })
            ->with(['projectManager.project'])
            ->firstOrFail();

        $projectId = $dailyReport->projectManager->project_id;

        DB::transaction(function () use ($dailyReport) {
            $dailyReport->delete();
        });

        return redirect()->route('pm.daily-reports.index', ['project_id' => $projectId])->with('success', 'Laporan harian berhasil dihapus.');
    }
}
