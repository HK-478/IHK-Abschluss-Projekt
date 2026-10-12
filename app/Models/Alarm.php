<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alarm extends Model
{
    // Erlaubt Mass-Assignment für diese Felder
    protected $fillable = [
        'user_id', 'watched_user_id', 'active',
        'triggered_at', 'detected_at', 'delivered_at', 'recipient_status'
    ];

    // Beziehungen zu den Usern
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function watchedUser()
    {
        return $this->belongsTo(User::class, 'watched_user_id');
    }

    // Methoden aus dem UML
    public function activate()
    {
        $this->update(['active' => true]);
    }

    public function deactivate()
    {
        $this->update(['active' => false]);
    }
}
