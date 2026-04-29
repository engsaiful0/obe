<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_name',
        'description',
        'start_stoppage_id',
        'end_stoppage_id',
        'distance',
        'estimated_time',
        'is_active',
        'user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'distance' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with Start Stoppage
    public function startStoppage()
    {
        return $this->belongsTo(Stoppage::class, 'start_stoppage_id');
    }

    // Relationship with End Stoppage
    public function endStoppage()
    {
        return $this->belongsTo(Stoppage::class, 'end_stoppage_id');
    }
}
