<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Shift extends Model
{

    protected $fillable=[
        'name',
        'time_in',
        'time_out'
    ];

    protected $casts = [
        'time_in' => 'datetime',
        'time_out' => 'datetime',
    ];

    public function users(){
        return $this->hasMany(User::class);
    }
}
