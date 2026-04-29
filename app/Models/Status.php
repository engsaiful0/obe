<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_name',
        'related_to',
        'user_id',
    ];
    
    // Related to options
    public static function getRelatedToOptions()
    {
        return [
            'bus-helper' => 'Bus Helper',
            'bus' => 'Bus',
            'bus-schedule' => 'Bus Schedule',
            'driver' => 'Driver',
            'driver-helper-assignment' => 'Driver & Helper Assignment',
            'employee' => 'Employee',
          
            
        ];
    }

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
