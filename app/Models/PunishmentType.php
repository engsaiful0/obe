<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PunishmentType extends Model
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
     * Get the user who created the punishment type
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get punishments that use this punishment type
     */
    public function punishments()
    {
        return $this->hasMany(Punishment::class, 'punishment_type', 'name');
    }


    /**
     * Scope a query to only include punishment types for the current user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
