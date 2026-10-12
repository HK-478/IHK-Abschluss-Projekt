<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineStatus extends Model
{
    // Da dein ERM die Tabelle im Singular "online_status" nennt,
    // überschreiben wir hier Laravels Standard (welcher "online_statuses" erwarten würde)
    protected $table = 'online_status';

    // Mass-Assignment für diese Felder erlauben, wie im ERM definiert
    protected $fillable = [
        'user_id',
        'status',
        'last_seen_at'
    ];

    // Typisierung (Casting) der Attribute
    protected $casts = [
        'last_seen_at' => 'datetime',
        // Wenn du PHP 8.1+ nutzt, könntest du hier auch ein Enum für den Status hinterlegen
        // 'status' => StatusEnum::class,
    ];

    // Beziehung: Ein Online-Status-Eintrag gehört immer zu genau einem Benutzer
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
