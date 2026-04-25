<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectEmployee extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'project_manager_id',
        'employee_id',
        'project_role_id',
    ];

    public function projectManager()
    {
        return $this->belongsTo(ProjectManager::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function projectRole()
    {
        return $this->belongsTo(ProjectRole::class);
    }
}
