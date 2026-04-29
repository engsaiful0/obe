<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeploymentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'deployment_date',
        'trip_time_id',
        'bus_user_id',
        'deployment_type_id',
        'trip_type',
        'user_id',
        'remarks',
    ];

    protected $casts = [
        'deployment_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the trip time for this deployment plan
     */
    public function tripTime()
    {
        return $this->belongsTo(TripTime::class);
    }
    /**
     * Get the deployment type for this deployment plan
     */
    public function deploymentType()
    {
        return $this->belongsTo(DeploymentType::class);
    }

    /**
     * Get the bus user for this deployment plan
     */
    public function busUser()
    {
        return $this->belongsTo(BusUser::class);
    }

    /**
     * Get the user who created this deployment plan
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all items (stoppage-bus assignments) for this deployment plan
     */
    public function items()
    {
        return $this->hasMany(DeploymentPlanItem::class);
    }

    /**
     * Scope for filtering by date
     */
    public function scopeByDate($query, $date)
    {
        return $query->where('deployment_date', $date);
    }

    /**
     * Scope for filtering by trip time
     */
    public function scopeByTripTime($query, $tripTimeId)
    {
        return $query->where('trip_time_id', $tripTimeId);
    }

    /**
     * Scope for filtering by bus user
     */
    public function scopeByBusUser($query, $busUserId)
    {
        return $query->where('bus_user_id', $busUserId);
    }
}

