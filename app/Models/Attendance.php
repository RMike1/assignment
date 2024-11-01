<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Attendance extends Model
{
    use HasFactory;
    protected $guarded=[];

    protected function casts(): array
    {
        return [
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
        ];
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
