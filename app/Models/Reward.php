<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_id',
        'bus_sub_type_id',
        'reward_amount',
        'reward_date',
        'reason',
        'reward_type_id',
        'remarks',
        'document',
        'user_id',
    ];

    protected $casts = [
        'reward_date' => 'date',
        'reward_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the bus that owns the reward
     */
    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }
    public function busSubType()
    {
        return $this->belongsTo(BusSubType::class);
    }


    /**
     * Get the driver that owns the reward
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the assistant that owns the reward
     */
    public function busHelper()
    {
        return $this->belongsTo(BusHelper::class);
    }

    /**
     * Get the user who created the record
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reward type
     */
    public function rewardType()
    {
        return $this->belongsTo(RewardType::class);
    }
}
