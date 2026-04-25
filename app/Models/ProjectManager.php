<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectManager extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'project_id',
        'pm_id',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function pm()
    {
        return $this->belongsTo(Employee::class, 'pm_id');
    }

    public function projectEmployees()
    {
        return $this->hasMany(ProjectEmployee::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class);
    }
}
