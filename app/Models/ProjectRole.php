<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectRole extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'project_id',
        'role_name',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function projectEmployees()
    {
        return $this->hasMany(ProjectEmployee::class);
    }
}
