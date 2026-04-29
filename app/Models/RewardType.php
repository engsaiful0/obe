<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created the reward type
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get rewards that use this reward type
     */
    public function rewards()
    {
        return $this->hasMany(Reward::class, 'reward_type_id');
    }

    /**
     * Scope a query to only include reward types for the current user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}