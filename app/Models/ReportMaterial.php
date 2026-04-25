<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReportMaterial extends Model
{
    use HasUuids;
    protected $guarded = ['id'];

    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class);
    }
    public function projectMaterial()
    {
        return $this->belongsTo(ProjectMaterial::class);
    }
}
