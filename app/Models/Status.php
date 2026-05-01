<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_name',
        'related_to_id',
        'user_id',
    ];

    /**
     * Labels for Related To dropdown (Status settings), sourced from Related To CRUD.
     *
     * @return array<string, string>
     */
    public static function getRelatedToOptions()
    {
        return RelatedTo::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected $casts = [
        'related_to_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function relatedTo()
    {
        return $this->belongsTo(RelatedTo::class, 'related_to_id');
    }

    public function blooms()
    {
        return $this->hasMany(Bloom::class, 'status_id');
    }

    public function clos()
    {
        return $this->hasMany(Clo::class, 'status_id');
    }
}

