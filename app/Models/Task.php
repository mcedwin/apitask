<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'tasks';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'objective_id',
        'title',
        'expected_minutes',
        'scheduled_at',
        'completed_at',
        'status'
    ];

    public function objetive()
    {
        return $this->belongsTo(Objetive::class, 'objective_id');
    }
}