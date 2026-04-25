<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasUuids, SoftDeletes;

    protected $guarded = ['id'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function pm()
    {
        return $this->belongsTo(Employee::class, 'pm_id');
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
