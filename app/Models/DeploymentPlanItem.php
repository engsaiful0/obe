<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeploymentPlanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'deployment_plan_id',
        'stoppage_id',
        'bus_sub_type_id',
        'bus_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the deployment plan this item belongs to
     */
    public function deploymentPlan()
    {
        return $this->belongsTo(DeploymentPlan::class, 'deployment_plan_id');
    }

    /**
     * Get the stoppage for this item
     */
    public function stoppage()
    {
        return $this->belongsTo(Stoppage::class);
    }

    /**
     * Get the bus sub-type for this item
     */
    public function busSubType()
    {
        return $this->belongsTo(BusSubType::class);
    }

    /**
     * Get the bus assigned to this item
     */
    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }
}

