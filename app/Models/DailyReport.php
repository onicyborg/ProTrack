<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyReport extends Model
{
    use HasUuids, SoftDeletes;

    protected $guarded = ['id'];

    public function project()
    {
        return $this->belongsTo(Project::class);
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