<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Ein User gehört zu einem Team
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    // Alarme, die dieser User erstellt hat
    public function createdAlarms()
    {
        return $this->hasMany(Alarm::class, 'user_id');
    }

    // Alarme, bei denen dieser User beobachtet wird
    public function targetAlarms()
    {
        return $this->hasMany(Alarm::class, 'watched_user_id');
    }

    // Status-Verlauf
    public function onlineStatus()
    {
        return $this->hasMany(OnlineStatus::class);
    }
}
