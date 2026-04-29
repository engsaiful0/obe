<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;

    protected $fillable = [
        'color_name',
        'color_code',
        'color_view',
        'description',
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

    // Accessor for formatted color code
    public function getFormattedColorCodeAttribute()
    {
        return strtoupper($this->color_code);
    }

    // Accessor for color preview
    public function getColorPreviewAttribute()
    {
        return '<div style="width: 20px; height: 20px; background-color: ' . $this->color_code . '; border: 1px solid #ccc; border-radius: 3px;"></div>';
    }
}