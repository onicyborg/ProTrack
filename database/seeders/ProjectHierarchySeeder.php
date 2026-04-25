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

            $client = Client::create([
                'client_name' => 'PT Contoh Klien',
                'contact' => '081234567890',
                'address' => 'Jakarta',
            ]);

            $project = Project::create([
                'project_name' => 'Proyek Demo ProTrack',
                'client_id' => $client->id,
                'account_code' => 'AC-001',
                'budget_year' => date('Y'),
                'status' => 'active',
            ]);

            $roleWorker = ProjectRole::create([
                'project_id' => $project->id,
                'role_name' => 'Worker',
            ]);

            $pm1 = $pmEmployees->first();
            $pm2 = $pmEmployees->skip(1)->first() ?? $pm1;

            $pmLink1 = ProjectManager::create([
                'project_id' => $project->id,
                'pm_id' => $pm1->id,
            ]);

            $pmLink2 = ProjectManager::create([
                'project_id' => $project->id,
                'pm_id' => $pm2->id,
            ]);

            // Assign semua worker ke PM pertama (contoh hierarki)
            foreach ($workerEmployees as $worker) {
                ProjectEmployee::create([
                    'project_manager_id' => $pmLink1->id,
                    'employee_id' => $worker->id,
                    'project_role_id' => $roleWorker->id,
                ]);
            }

            // Pastikan ada minimal 1 worker pada PM kedua (biar kelihatan multi-PM)
            $firstWorker = $workerEmployees->first();
            if ($firstWorker) {
                ProjectEmployee::create([
                    'project_manager_id' => $pmLink2->id,
                    'employee_id' => $firstWorker->id,
                    'project_role_id' => $roleWorker->id,
                ]);
            }
        });
    }
}
