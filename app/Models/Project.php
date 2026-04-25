<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'project_name',
        'client_id',
        'account_code',
        'budget_year',
        'start_date',
        'end_date',
        'status',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function projectManagers()
    {
        return $this->hasMany(ProjectManager::class);
    }

    // Katalog Proyek
    public function materials()
    {
        return $this->hasMany(ProjectMaterial::class);
    }

    public function equipments()
    {
        return $this->hasMany(ProjectEquipment::class);
    }

    public function roles()
    {
        return $this->hasMany(ProjectRole::class);
    }

    // Operasional Proyek
    public function projectEmployees()
    {
        return $this->hasManyThrough(
            ProjectEmployee::class,
            ProjectManager::class,
            'project_id',
            'project_manager_id',
            'id',
            'id'
        );
    }

    public function tasks()
    {
        return $this->hasManyThrough(
            Task::class,
            ProjectManager::class,
            'project_id',
            'project_manager_id',
            'id',
            'id'
        );
    }

    public function dailyReports()
    {
        return $this->hasManyThrough(
            DailyReport::class,
            ProjectManager::class,
            'project_id',
            'project_manager_id',
            'id',
            'id'
        );
    }
}
