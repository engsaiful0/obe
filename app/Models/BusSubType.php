<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusSubType extends Model
{
    use HasFactory;

    const OWN_BUS_SUB_TYPE_ID = 1;
    const BRTC_BUS_SUB_TYPE_ID = 2;
    const HIRED_BUS_SUB_TYPE_ID = 3;

    protected $fillable = [
        'sub_type_name',
        'user_id',
    ];

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
