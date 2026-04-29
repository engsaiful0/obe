<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelperUniqueId extends Model
{
    use HasFactory;

    protected $table = 'helper_unique_ids';

    protected $fillable = [
        'bus_helper_unique_id',
        'serial',
        'bus_helper_id',
        'user_id'
    ];

    public function busHelper()
    {
        return $this->belongsTo(BusHelper::class, 'bus_helper_id');
    }

    public function assistant()
    {
        return $this->belongsTo(BusHelper::class, 'bus_helper_id');
    }
}
