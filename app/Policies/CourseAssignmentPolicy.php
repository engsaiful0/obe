<?php

namespace App\Policies;

use App\Models\CourseAssignment;
use App\Models\User;

class CourseAssignmentPolicy
{
    public function view(User $user, CourseAssignment $courseAssignment): bool
    {
        $ruleName = strtolower((string) ($user->rule?->name ?? ''));
        if ($ruleName !== 'teacher') {
            return false;
        }

        $teacherId = (int) ($user->teacher?->id ?? 0);

        return $teacherId > 0 && $teacherId === (int) $courseAssignment->teacher_id;
    }
}
