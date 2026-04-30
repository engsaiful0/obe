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

    /**
     * Labels for Related To dropdown (Status settings), sourced from Related To CRUD.
     *
     * @return array<string, string>
     */
    public static function getRelatedToOptions()
    {
        return RelatedTo::query()
            ->orderBy('name')
            ->pluck('name', 'name')
            ->all();
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
