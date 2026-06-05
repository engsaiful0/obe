<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAttendance extends Model
{
    public const STATUS_PRESENT = 'present';

    public const STATUS_ABSENT = 'absent';

    public const STATUS_LATE = 'late';

    public const STATUS_EXCUSED = 'excused';

    protected $fillable = [
        'course_assignment_id',
        'student_id',
        'attendance_date',
        'status',
        'remarks',
        'recorded_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    public function courseAssignment(): BelongsTo
    {
        return $this->belongsTo(CourseAssignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
