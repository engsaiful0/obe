<?php

namespace App\Policies;

use App\Models\CourseAssignment;
use App\Models\User;
use App\Services\TeacherCourseService;

class CourseAssignmentPolicy
{
    public function view(User $user, CourseAssignment $courseAssignment): bool
    {
        if ($this->isAssignedTeacher($user, $courseAssignment)) {
            return true;
        }

        if ($this->isAdminRole($user)) {
            return true;
        }

        if ($user->hasPermissionTo('obe-settings-view')) {
            return true;
        }

        return $this->isOversightRole($user, $courseAssignment);
    }

    public function manage(User $user, CourseAssignment $courseAssignment): bool
    {
        if ($this->isAdminRole($user)) {
            return true;
        }

        if (! $this->isAssignedTeacher($user, $courseAssignment)) {
            return false;
        }

        return app(TeacherCourseService::class)->isCurrentAssignment($courseAssignment);
    }

    private function isAssignedTeacher(User $user, CourseAssignment $courseAssignment): bool
    {
        $ruleName = strtolower((string) ($user->rule?->name ?? ''));
        if ($ruleName !== 'teacher') {
            return false;
        }

        $teacherId = (int) ($user->teacher?->id ?? 0);

        return $teacherId > 0 && $teacherId === (int) $courseAssignment->teacher_id;
    }

    private function isAdminRole(User $user): bool
    {
        $ruleName = strtolower((string) ($user->rule?->name ?? ''));

        return in_array($ruleName, ['super admin', 'admin'], true);
    }

    private function isOversightRole(User $user, CourseAssignment $courseAssignment): bool
    {
        $teacher = $user->teacher;
        if (! $teacher) {
            return false;
        }

        $courseAssignment->loadMissing('program');

        if ($teacher->is_program_coordinator || $teacher->is_course_coordinator) {
            return (int) $teacher->department_id === (int) ($courseAssignment->program?->department_id ?? 0)
                || (int) $teacher->department_id === 0;
        }

        $department = $courseAssignment->program?->department;
        if ($department && trim((string) $department->head_chairman_name) !== '') {
            $chairName = strtolower(trim((string) $department->head_chairman_name));
            $teacherName = strtolower(trim((string) $teacher->teacher_name));

            return $chairName !== '' && $chairName === $teacherName;
        }

        return false;
    }
}
