<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TaskSubtask extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
