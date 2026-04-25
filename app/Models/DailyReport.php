<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyReport extends Model
{
    use HasUuids, SoftDeletes;

    protected $guarded = ['id'];

    public function projectManager()
    {
        return $this->belongsTo(ProjectManager::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function executor()
    {
        return $this->belongsTo(Employee::class, 'executor_id');
    }

    public function works()
    {
        return $this->hasMany(ReportWork::class);
    }

    public function materials()
    {
        return $this->hasMany(ReportMaterial::class);
    }

    public function equipments()
    {
        return $this->hasMany(ReportEquipment::class);
    }
}