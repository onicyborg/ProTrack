<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_name',
        'position',
        'nik',
        'phone_number',
        'birth_date',
        'gender',
        'address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Proyek di mana karyawan ini menjadi Project Manager (multi-PM)
    public function projectsAsPm()
    {
        return $this->belongsToMany(Project::class, 'project_managers', 'pm_id', 'project_id')
            ->wherePivotNull('deleted_at')
            ->distinct();
    }

    public function projectManagers()
    {
        return $this->hasMany(ProjectManager::class, 'pm_id');
    }

    public function projectEmployees()
    {
        return $this->hasMany(ProjectEmployee::class);
    }

    public function taskLogs()
    {
        return $this->hasMany(EmployeeTaskLog::class);
    }
}
