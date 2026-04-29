<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bus_schedule_keyword_id',
        'status_id',
        'bus_user_id',
        'effective_from',
        'description',
        'user_id',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function busUser()
    {
        return $this->belongsTo(BusUser::class);
    }

    public function keyword()
    {
        return $this->belongsTo(BusScheduleKeyword::class, 'bus_schedule_keyword_id');
    }
    
    /**
     * Get the status
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }
    
    /**
     * Get all schedule entries for this bus schedule
     */
    public function entries()
    {
        return $this->hasMany(BusScheduleEntry::class)->orderBy('sort_order');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereHas('status', function($q) {
            $q->where('status_name', 'like', '%active%');
        });
    }

    public function scopeByBusUser($query, $busUserId)
    {
        return $query->where('bus_user_id', $busUserId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereHas('tripTime', function($q) use ($date) {
            $q->whereDate('created_at', $date);
        });
    }
}
