<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasUuids, SoftDeletes;

    protected $guarded = ['id'];

    public function projectManager()
    {
        return $this->belongsTo(ProjectManager::class);
    }

    public function assignments()
    {
        return $this->hasMany(TaskAssignment::class);
    }

    public function subtasks()
    {
        return $this->hasMany(TaskSubtask::class);
    }

    public function logs()
    {
        return $this->hasMany(EmployeeTaskLog::class);
    }
}