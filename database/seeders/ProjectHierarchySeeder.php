<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\DailyReport;
use App\Models\Employee;
use App\Models\EmployeeTaskLog;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Models\ProjectEquipment;
use App\Models\ProjectManager;
use App\Models\ProjectMaterial;
use App\Models\ProjectRole;
use App\Models\ReportEquipment;
use App\Models\ReportMaterial;
use App\Models\ReportWork;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskSubtask;
use Carbon\CarbonPeriod;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ProjectHierarchySeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create('id_ID');

        $roleNames = [
            'Mandor',
            'Tukang',
            'Operator',
            'Helper',
            'Surveyor',
            'K3',
            'QA/QC',
        ];

        $materialTemplates = [
            ['material_name' => 'Semen Portland', 'unit' => 'zak'],
            ['material_name' => 'Pasir', 'unit' => 'm3'],
            ['material_name' => 'Batu Split', 'unit' => 'm3'],
            ['material_name' => 'Besi Beton D10', 'unit' => 'batang'],
            ['material_name' => 'Besi Beton D13', 'unit' => 'batang'],
            ['material_name' => 'Wiremesh M8', 'unit' => 'lembar'],
            ['material_name' => 'Pipa PVC 4 inch', 'unit' => 'batang'],
            ['material_name' => 'Cat Marka Jalan', 'unit' => 'liter'],
        ];

        $equipmentTemplates = [
            ['equipment_name' => 'Excavator', 'unit' => 'jam'],
            ['equipment_name' => 'Dump Truck', 'unit' => 'rit'],
            ['equipment_name' => 'Concrete Mixer', 'unit' => 'jam'],
            ['equipment_name' => 'Stamper', 'unit' => 'jam'],
            ['equipment_name' => 'Vibrator Beton', 'unit' => 'jam'],
        ];

        $taskTemplates = [
            'Persiapan & Mobilisasi',
            'Pekerjaan Tanah',
            'Pekerjaan Pondasi',
            'Pekerjaan Struktur Beton',
            'Pekerjaan Drainase',
            'Pekerjaan Pasangan Batu',
            'Pekerjaan Aspal',
            'Finishing & Pembersihan',
        ];

        DB::transaction(function () use ($faker, $roleNames, $materialTemplates, $equipmentTemplates, $taskTemplates) {
            $pmEmployees = Employee::query()
                ->whereHas('user', function ($q) {
                    $q->where('role', 'pm');
                })
                ->get();

            $workerEmployees = Employee::query()
                ->whereNull('user_id')
                ->get();

            if ($pmEmployees->count() < 2 || $workerEmployees->count() < 10) {
                return;
            }

            $clientsData = [
                ['client_name' => 'Dinas PUPR Kota Bandung', 'contact' => '022-1234567', 'address' => 'Bandung, Jawa Barat'],
                ['client_name' => 'Dinas Perhubungan Provinsi Jawa Timur', 'contact' => '031-7654321', 'address' => 'Surabaya, Jawa Timur'],
                ['client_name' => 'PT Nusantara Konstruksi', 'contact' => '021-777888', 'address' => 'Jakarta'],
                ['client_name' => 'PT Sarana Infrastruktur Mandiri', 'contact' => '0274-112233', 'address' => 'Yogyakarta'],
            ];

            $clients = collect($clientsData)->map(function ($c) {
                return Client::firstOrCreate(['client_name' => $c['client_name']], $c);
            })->values();

            $projectsData = [
                ['name' => 'Peningkatan Jalan Lingkar Barat', 'city' => 'Bandung', 'status' => 'active'],
                ['name' => 'Rehabilitasi Drainase Kawasan Industri', 'city' => 'Surabaya', 'status' => 'active'],
                ['name' => 'Pembangunan Jembatan Sungai Citarum', 'city' => 'Karawang', 'status' => 'active'],
                ['name' => 'Pemeliharaan Berkala Jalan Provinsi', 'city' => 'Malang', 'status' => 'active'],
                ['name' => 'Pembangunan Saluran Irigasi Desa', 'city' => 'Sleman', 'status' => 'active'],
                ['name' => 'Perbaikan Trotoar & Marka', 'city' => 'Jakarta', 'status' => 'active'],
            ];

            $projects = collect();
            foreach ($projectsData as $idx => $p) {
                $client = $clients->get($idx % $clients->count());

                $start = Carbon::now()->subDays(40)->addDays($idx * 7)->toDateString();
                $end = Carbon::parse($start)->addDays(120 + ($idx * 10))->toDateString();

                $project = Project::firstOrCreate(
                    ['project_name' => $p['name'], 'client_id' => $client->id],
                    [
                        'account_code' => 'AC-' . str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT),
                        'budget_year' => Carbon::parse($start)->format('Y'),
                        'start_date' => $start,
                        'end_date' => $end,
                        'status' => $p['status'],
                    ]
                );

                $projects->push($project);

                foreach ($roleNames as $rn) {
                    $role = ProjectRole::withTrashed()->firstOrCreate([
                        'project_id' => $project->id,
                        'role_name' => $rn,
                    ]);
                    if ($role->trashed()) {
                        $role->restore();
                    }
                }

                foreach ($materialTemplates as $m) {
                    $mat = ProjectMaterial::withTrashed()->firstOrCreate([
                        'project_id' => $project->id,
                        'material_name' => $m['material_name'],
                    ], [
                        'unit' => $m['unit'],
                    ]);
                    if ($mat->trashed()) {
                        $mat->restore();
                    }
                }

                foreach ($equipmentTemplates as $e) {
                    $eq = ProjectEquipment::withTrashed()->firstOrCreate([
                        'project_id' => $project->id,
                        'equipment_name' => $e['equipment_name'],
                    ], [
                        'unit' => $e['unit'],
                    ]);
                    if ($eq->trashed()) {
                        $eq->restore();
                    }
                }
            }

            foreach ($projects as $idx => $project) {
                $pm1 = $pmEmployees->get($idx % $pmEmployees->count());
                $pm2 = $pmEmployees->get(($idx + 1) % $pmEmployees->count());

                $pmLinks = collect();

                $pmLink1 = ProjectManager::withTrashed()->firstOrCreate([
                    'project_id' => $project->id,
                    'pm_id' => $pm1->id,
                ]);
                if ($pmLink1->trashed()) {
                    $pmLink1->restore();
                }
                $pmLinks->push($pmLink1);

                if ($pm2 && $pm2->id !== $pm1->id) {
                    $pmLink2 = ProjectManager::withTrashed()->firstOrCreate([
                        'project_id' => $project->id,
                        'pm_id' => $pm2->id,
                    ]);
                    if ($pmLink2->trashed()) {
                        $pmLink2->restore();
                    }
                    $pmLinks->push($pmLink2);
                }

                $projectRoles = ProjectRole::query()
                    ->where('project_id', $project->id)
                    ->whereNull('deleted_at')
                    ->get();

                $projectMaterials = ProjectMaterial::query()
                    ->where('project_id', $project->id)
                    ->whereNull('deleted_at')
                    ->get();

                $projectEquipments = ProjectEquipment::query()
                    ->where('project_id', $project->id)
                    ->whereNull('deleted_at')
                    ->get();

                foreach ($pmLinks as $pmLink) {
                    $team = $workerEmployees->shuffle()->take($faker->numberBetween(12, 20))->values();

                    foreach ($team as $w) {
                        $role = $projectRoles->random();

                        $pe = ProjectEmployee::withTrashed()->firstOrCreate([
                            'project_manager_id' => $pmLink->id,
                            'employee_id' => $w->id,
                        ], [
                            'project_role_id' => $role->id,
                        ]);
                        if ($pe->trashed()) {
                            $pe->restore();
                        }
                        if ($pe->project_role_id !== $role->id) {
                            $pe->update(['project_role_id' => $role->id]);
                        }
                    }

                    $tasks = collect();
                    foreach ($taskTemplates as $tName) {
                        $start = $project->start_date ? Carbon::parse($project->start_date) : Carbon::now()->subDays(15);
                        $end = $project->end_date ? Carbon::parse($project->end_date) : Carbon::now()->addDays(60);

                        $taskStart = $start->copy()->addDays($faker->numberBetween(0, 20));
                        $taskEnd = $taskStart->copy()->addDays($faker->numberBetween(5, 25));
                        if ($taskEnd->greaterThan($end)) {
                            $taskEnd = $end->copy();
                        }

                        $task = Task::withTrashed()->firstOrCreate([
                            'project_manager_id' => $pmLink->id,
                            'task_name' => $tName,
                        ], [
                            'start_date' => $taskStart->toDateString(),
                            'end_date' => $taskEnd->toDateString(),
                        ]);

                        if ($task->trashed()) {
                            $task->restore();
                        }

                        $tasks->push($task);

                        $subtaskCount = $faker->numberBetween(0, 3);
                        for ($s = 1; $s <= $subtaskCount; $s++) {
                            TaskSubtask::firstOrCreate([
                                'task_id' => $task->id,
                                'subtask_name' => $tName . ' - Item ' . $s,
                            ]);
                        }
                    }

                    $teamIds = ProjectEmployee::query()
                        ->where('project_manager_id', $pmLink->id)
                        ->whereNull('deleted_at')
                        ->pluck('employee_id')
                        ->all();

                    foreach ($tasks as $task) {
                        $assignees = collect($teamIds)->shuffle()->take($faker->numberBetween(3, 8));
                        foreach ($assignees as $eid) {
                            TaskAssignment::firstOrCreate([
                                'task_id' => $task->id,
                                'employee_id' => $eid,
                            ]);
                        }
                    }

                    $projectStart = $project->start_date ? Carbon::parse($project->start_date) : Carbon::now()->subDays(30);
                    $projectEnd = $project->end_date ? Carbon::parse($project->end_date) : Carbon::now();

                    $rangeStart = $projectStart->greaterThan(Carbon::now()->subDays(30)) ? $projectStart->copy() : Carbon::now()->subDays(30);
                    $rangeEnd = $projectEnd->lessThan(Carbon::now()) ? $projectEnd->copy() : Carbon::now();

                    if ($rangeStart->greaterThan($rangeEnd)) {
                        continue;
                    }

                    $dates = collect(CarbonPeriod::create($rangeStart, $rangeEnd))
                        ->map(fn($d) => $d->toDateString())
                        ->shuffle()
                        ->take($faker->numberBetween(6, 12))
                        ->values();

                    foreach ($dates as $dateStr) {
                        $tasksActive = $tasks->filter(function (Task $t) use ($dateStr) {
                            $sd = $t->start_date ? Carbon::parse($t->start_date)->toDateString() : null;
                            $ed = $t->end_date ? Carbon::parse($t->end_date)->toDateString() : null;

                            if ($sd && $sd > $dateStr) return false;
                            if ($ed && $ed < $dateStr) return false;
                            return true;
                        })->values();

                        if ($tasksActive->isEmpty()) {
                            continue;
                        }

                        $weather = $faker->randomElement(['Cerah', 'Mendung', 'Hujan']);
                        $weatherTime = $faker->randomElement(['Pagi', 'Siang', 'Sore']);

                        $supervisorId = collect($teamIds)->shuffle()->first();
                        $executorId = collect($teamIds)->shuffle()->first();

                        $supervisor = $supervisorId ? Employee::query()->find($supervisorId) : null;
                        $executor = $executorId ? Employee::query()->find($executorId) : null;

                        $dailyReport = DailyReport::create([
                            'project_manager_id' => $pmLink->id,
                            'report_date' => $dateStr,
                            'kegiatan' => $faker->randomElement(['Pekerjaan Jalan', 'Pekerjaan Drainase', 'Pekerjaan Struktur', 'Pemeliharaan', 'Pekerjaan Irigasi']),
                            'rincian_kegiatan' => $faker->sentence(6),
                            'lokasi_kegiatan' => $faker->city,
                            'weather_condition' => $weather,
                            'weather_time' => $weatherTime,
                            'weather_notes' => $faker->sentence(10),
                            'supervisor_id' => $supervisorId,
                            'executor_id' => $executorId,
                            'supervisor_name' => $supervisor?->employee_name,
                            'executor_name' => $executor?->employee_name,
                        ]);

                        $works = $tasksActive->shuffle()->take($faker->numberBetween(1, 3))->values();
                        foreach ($works as $w) {
                            ReportWork::create([
                                'daily_report_id' => $dailyReport->id,
                                'task_id' => $w->id,
                                'volume' => $faker->randomFloat(2, 1, 150),
                            ]);
                        }

                        $attendanceTaskId = $works->first()?->id;

                        $materialsPicked = $projectMaterials->shuffle()->take($faker->numberBetween(0, 3));
                        foreach ($materialsPicked as $m) {
                            ReportMaterial::create([
                                'daily_report_id' => $dailyReport->id,
                                'project_material_id' => $m->id,
                                'volume' => $faker->randomFloat(2, 1, 50),
                                'notes' => $faker->randomElement([null, 'Pengiriman lancar', 'Stok terbatas', 'Sesuai spek']),
                            ]);
                        }

                        $equipPicked = $projectEquipments->shuffle()->take($faker->numberBetween(0, 2));
                        foreach ($equipPicked as $e) {
                            ReportEquipment::create([
                                'daily_report_id' => $dailyReport->id,
                                'project_equipment_id' => $e->id,
                                'volume' => $faker->randomFloat(2, 1, 12),
                            ]);
                        }

                        if ($attendanceTaskId) {
                            $present = collect($teamIds)->shuffle()->take($faker->numberBetween(5, 15));
                            foreach ($present as $eid) {
                                EmployeeTaskLog::create([
                                    'task_id' => $attendanceTaskId,
                                    'employee_id' => $eid,
                                    'log_date' => $dateStr,
                                    'notes' => $faker->randomElement([null, 'Lembur', 'Masuk siang', 'Sakit ringan', 'Alat terbatas']),
                                ]);
                            }
                        }
                    }
                }
            }
        });
    }
}
