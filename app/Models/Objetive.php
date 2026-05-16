<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Objetive extends Model
{
    protected $table = 'objectives';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false; // si tu tabla no tiene timestamps

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'status'
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class, 'objective_id');
    }

    // app/Models/Objetive.php

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}