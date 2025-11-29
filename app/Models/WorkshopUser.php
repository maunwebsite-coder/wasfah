<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkshopUser extends Model
{
    use HasFactory;

    protected $table = 'workshop_user';

    protected $fillable = [
        'workshop_id',
        'user_id',
        'status',
        'attendance_status',
        'has_recording_access',
        'attended_at',
        'recording_unlocked_at',
        'notes',
    ];

    protected $casts = [
        'has_recording_access' => 'boolean',
        'attended_at' => 'datetime',
        'recording_unlocked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }
}
