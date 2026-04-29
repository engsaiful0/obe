<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'opening_stock',
        'related_to',
        'user_id',
    ];

    protected $casts = [
        'opening_stock' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with PurchaseItems
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    // Relationship with IssueItems
    public function issueItems()
    {
        return $this->hasMany(IssueItem::class);
    }
}
