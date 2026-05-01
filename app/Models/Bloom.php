<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bloom extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'level_order',
        'description',
        'status_id',
    ];

    protected $casts = [
        'level_order' => 'integer',
        'status_id' => 'integer',
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
