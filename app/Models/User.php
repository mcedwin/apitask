<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'name',
        'email',
        'timezone'
    ];

    protected $hidden = [];

    public function goals()
    {
        return $this->hasMany(Objetive::class, 'user_id');
    }
}