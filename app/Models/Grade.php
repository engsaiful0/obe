<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_marks',
        'to_marks',
        'grade_name',
        'grade_point',
    ];

    protected $casts = [
        'from_marks' => 'decimal:2',
        'to_marks' => 'decimal:2',
        'grade_point' => 'decimal:2',
    ];
}
