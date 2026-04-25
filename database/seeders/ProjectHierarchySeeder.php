<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Models\ProjectManager;
use App\Models\ProjectRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectHierarchySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $pmEmployees = Employee::query()
                ->whereHas('user', function ($q) {
                    $q->where('role', 'pm');
                })
                ->get();

            $workerEmployees = Employee::query()
                ->whereNull('user_id')
                ->get();

            if ($pmEmployees->count() < 1 || $workerEmployees->count() < 1) {
                return;
            }

            $client = Client::firstOrCreate(
                ['client_name' => 'PT Contoh Klien'],
                [
                    'contact' => '081234567890',
                    'address' => 'Jakarta',
                ]
            );

            $project = Project::firstOrCreate(
                [
                    'project_name' => 'Proyek Demo Multi-PM',
                    'client_id' => $client->id,
                ],
                [
                    'account_code' => 'AC-001',
                    'budget_year' => '2026',
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addMonths(3)->toDateString(),
                    'status' => 'active',
                ]
            );

            $roleWorker = ProjectRole::withTrashed()->firstOrCreate([
                'project_id' => $project->id,
                'role_name' => 'Worker',
            ]);

            if ($roleWorker->trashed()) {
                $roleWorker->restore();
            }

            $pm1 = $pmEmployees->first();
            $pm2 = $pmEmployees->skip(1)->first();

            if (!$pm2) {
                return;
            }

            $pmLink1 = ProjectManager::withTrashed()->firstOrCreate([
                'project_id' => $project->id,
                'pm_id' => $pm1->id,
            ]);

            if ($pmLink1->trashed()) {
                $pmLink1->restore();
            }

            $pmLink2 = ProjectManager::withTrashed()->firstOrCreate([
                'project_id' => $project->id,
                'pm_id' => $pm2->id,
            ]);

            if ($pmLink2->trashed()) {
                $pmLink2->restore();
            }

            // Assign semua worker ke PM pertama (contoh hierarki)
            foreach ($workerEmployees as $worker) {
                ProjectEmployee::firstOrCreate([
                    'project_manager_id' => $pmLink1->id,
                    'employee_id' => $worker->id,
                ], [
                    'project_role_id' => $roleWorker->id,
                ]);
            }

            // Pastikan ada minimal 1 worker pada PM kedua (biar kelihatan multi-PM)
            $firstWorker = $workerEmployees->first();
            if ($firstWorker) {
                ProjectEmployee::firstOrCreate([
                    'project_manager_id' => $pmLink2->id,
                    'employee_id' => $firstWorker->id,
                ], [
                    'project_role_id' => $roleWorker->id,
                ]);
            }
        });
    }
}
