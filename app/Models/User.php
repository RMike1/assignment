<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Attendance;
use App\Models\Shift;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'time_in' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function attendances(){
        return $this->hasMany(Attendance::class);
    }

    public function shift(){
        return $this->belongsTo(Shift::class);
    }

    public function sendPasswordResetNotification($token)
    {

        $url = 'https://127.0.0.1:8000/reset-password?token=' . $token;

        $this->notify(new ResetPasswordNotification($url));
    }
    
}
