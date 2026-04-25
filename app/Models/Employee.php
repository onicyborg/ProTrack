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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Proyek di mana karyawan ini menjadi Project Manager
    public function projectsAsPm()
    {
        return $this->hasMany(Project::class, 'pm_id');
    }

    public function taskLogs()
    {
        return $this->hasMany(EmployeeTaskLog::class);
    }
}
