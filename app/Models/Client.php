<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'client_name',
        'contact',
        'address',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}