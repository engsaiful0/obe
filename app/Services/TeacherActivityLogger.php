<?php

namespace App\Services;

use App\Models\CourseAssignment;
use App\Models\Teacher;
use App\Models\TeacherActivityLog;
use Illuminate\Support\Facades\Auth;

class TeacherActivityLogger
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function log(
        string $action,
        ?string $description = null,
        ?CourseAssignment $assignment = null,
        array $meta = [],
        string $module = 'teacher_courses'
    ): void {
        $user = Auth::user();
        $teacherId = (int) ($user?->teacher?->id ?? 0);

        TeacherActivityLog::query()->create([
            'user_id' => $user?->id,
            'teacher_id' => $teacherId > 0 ? $teacherId : null,
            'course_assignment_id' => $assignment?->id,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => request()->ip(),
            'meta' => $meta === [] ? null : $meta,
        ]);
    }

    public function logForTeacher(
        Teacher $teacher,
        string $action,
        ?string $description = null,
        ?CourseAssignment $assignment = null,
        array $meta = []
    ): void {
        TeacherActivityLog::query()->create([
            'user_id' => $teacher->user_id,
            'teacher_id' => $teacher->id,
            'course_assignment_id' => $assignment?->id,
            'action' => $action,
            'module' => 'teacher_courses',
            'description' => $description,
            'ip_address' => request()->ip(),
            'meta' => $meta === [] ? null : $meta,
        ]);
    }
}
