<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distance extends Model
{
    use HasFactory;

    protected $fillable = [
        'distance_name',
        'start_stoppage_id',
        'end_stoppage_id',
        'distance_km',
        'description',
        'status',
        'user_id'
    ];

    protected $casts = [
        'distance_km' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function startStoppage()
    {
        return $this->belongsTo(Stoppage::class, 'start_stoppage_id');
    }

    public function endStoppage()
    {
        return $this->belongsTo(Stoppage::class, 'end_stoppage_id');
    }

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    public static function getStatusOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    // Accessors
    public function getFormattedDistanceAttribute()
    {
        return number_format((float) $this->distance_km, 2) . ' KM';
    }

    public function getRouteNameAttribute()
    {
        if ($this->distance_name) {
            return $this->distance_name;
        }
        
        return $this->startStoppage->stoppage_name . ' → ' . $this->endStoppage->stoppage_name;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }
}
