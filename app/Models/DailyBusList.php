<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyBusList extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_date',
        'bus_id',
        'start_stoppage_id',
        'end_stoppage_id',
        'trip_time_id',
        'bus_sub_type_id',
        'remarks',
        'user_id',
    ];

    protected $casts = [
        'list_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function startStoppage()
    {
        return $this->belongsTo(Stoppage::class, 'start_stoppage_id');
    }

    public function endStoppage()
    {
        return $this->belongsTo(Stoppage::class, 'end_stoppage_id');
    }


    public function busSubType()
    {
        return $this->belongsTo(BusSubType::class, 'bus_sub_type_id');
    }

    public function tripTime()
    {
        return $this->belongsTo(TripTime::class);
    }


    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('list_date', $date);
    }



    public function scopeForBus($query, $busId)
    {
        return $query->where('bus_id', $busId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('list_date', [$startDate, $endDate]);
    }
}
