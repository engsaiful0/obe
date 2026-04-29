<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_number',
        'supplier_id',
        'date',
        'paid',
        'due',
        'net_total',
        'payment_method_id',
        'remarks',
        'user_id',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'date' => 'date',
        'paid' => 'decimal:2',
        'due' => 'decimal:2',
        'net_total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with Supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    // Relationship with PurchaseItems
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

  
}
