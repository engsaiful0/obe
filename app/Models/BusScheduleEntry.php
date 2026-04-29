<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusScheduleEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_schedule_id',
        'start_time',
        'starting_point_id',
        'bus_route_id',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the bus schedule that owns this entry
     */
    public function busSchedule()
    {
        return $this->belongsTo(BusSchedule::class);
    }

    /**
     * Get the starting point stoppage
     */
    public function startingPoint()
    {
        return $this->belongsTo(Stoppage::class, 'starting_point_id');
    }

    /**
     * Get the bus route
     */
    public function busRoute()
    {
        return $this->belongsTo(BusRoute::class);
    }
}
