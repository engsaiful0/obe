<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverUniqueId extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_unique_id',
        'serial',
        'driver_id',
        'user_id'
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
