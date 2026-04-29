<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DamageItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'damage_id',
        'item_id',
        'quantity',
        'reason',
        'approximate',
        'user_id',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'approximate' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function damage()
    {
        return $this->belongsTo(Damage::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}


