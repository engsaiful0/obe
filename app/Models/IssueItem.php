<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssueItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'issue_id',
        'item_id',
        'unit_id',
        'user_id',
        'quantity',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationship with Issue
    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }

    // Relationship with Item
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // Relationship with Unit
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
