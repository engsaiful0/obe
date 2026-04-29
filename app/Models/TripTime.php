<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'time_name',
        'time_value',
        'time_period',
        'description',
        'user_id',
    ];

    protected $casts = [
        'time_value' => 'datetime:H:i',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
