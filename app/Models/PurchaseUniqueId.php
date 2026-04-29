<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseUniqueId extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_number',
        'serial',
        'purchase_id',
        'user_id'
    ];

    public function student()
    {
        return $this->belongsTo(Purchase::class);
    }
}
