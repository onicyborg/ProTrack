<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReportEquipment extends Model
{
    use HasUuids;
    protected $guarded = ['id'];

    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class);
    }
    public function projectEquipment()
    {
        return $this->belongsTo(ProjectEquipment::class);
    }
}
