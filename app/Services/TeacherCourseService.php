<?php

namespace App\Services;

use App\Models\CourseAssignment;
use App\Models\CourseFileDocument;
use App\Models\Teacher;
use App\Support\CourseFileDocumentTypes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TeacherCourseService
{
    public function __construct(
        protected TeacherCourseMarksService $marksService,
    ) {}

    public function isCurrentAssignment(CourseAssignment $assignment): bool
    {
        $assignment->loadMissing('academicSession');

        return strtolower((string) ($assignment->academicSession?->status ?? '')) === 'active';
    }

    public function isPreviousAssignment(CourseAssignment $assignment): bool
    {
        return ! $this->isCurrentAssignment($assignment);
    }

    public function courseStatusLabel(CourseAssignment $assignment): string
    {
        return $this->isCurrentAssignment($assignment) ? 'Current' : 'Previous';
    }

    /**
     * @return Builder<CourseAssignment>
     */
    public function baseQueryForTeacher(Teacher $teacher): Builder
    {
        return CourseAssignment::query()
            ->with([
                'course:id,course_code,course_title,credit',
                'program:id,program_name,program_code',
                'semester:id,semester_name',
                'academicSession:id,session_name,academic_year,status',
                'section:id,section_code,section_name',
                'teacher:id,teacher_name',
                'status:id,status_name',
            ])
            ->where('teacher_id', (int) $teacher->id);
    }

    /**
     * @return Builder<CourseAssignment>
     */
    public function assignedQuery(Teacher $teacher, ?string $search = null, ?string $sort = null, string $direction = 'desc'): Builder
    {
        $query = $this->baseQueryForTeacher($teacher);
        $this->applySearch($query, $search);
        $this->applySort($query, $sort, $direction);

        return $query;
    }

    /**
     * @return Builder<CourseAssignment>
     */
    public function currentQuery(Teacher $teacher, ?string $search = null, ?string $sort = null, string $direction = 'desc'): Builder
    {
        $query = $this->assignedQuery($teacher, $search, $sort, $direction)
            ->whereHas('academicSession', fn (Builder $q) => $q->where('status', 'Active'));

        return $query;
    }

    /**
     * @return Builder<CourseAssignment>
     */
    public function previousQuery(Teacher $teacher, ?string $search = null, ?string $sort = null, string $direction = 'desc'): Builder
    {
        $query = $this->assignedQuery($teacher, $search, $sort, $direction)
            ->whereHas('academicSession', fn (Builder $q) => $q->where('status', '!=', 'Active'));

        return $query;
    }

    public function paginateForTeacher(
        Teacher $teacher,
        string $scope,
        ?string $search = null,
        ?string $sort = null,
        string $direction = 'desc',
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = match ($scope) {
            'current' => $this->currentQuery($teacher, $search, $sort, $direction),
            'previous' => $this->previousQuery($teacher, $search, $sort, $direction),
            default => $this->assignedQuery($teacher, $search, $sort, $direction),
        };

        $paginator = $query->paginate($perPage)->withQueryString();
        $paginator->getCollection()->transform(fn (CourseAssignment $a) => $this->enrichAssignment($a, $scope));

        return $paginator;
    }

    /**
     * @return Collection<int, CourseAssignment>
     */
    public function allAssignedForExport(Teacher $teacher, ?string $search = null): Collection
    {
        $query = $this->assignedQuery($teacher, $search);
        $items = $query->get();

        return $items->map(fn (CourseAssignment $a) => $this->enrichAssignment($a, 'assigned'));
    }

    public function enrichAssignment(CourseAssignment $assignment, string $context = 'assigned'): CourseAssignment
    {
        $totalStudents = $this->marksService->studentsForAssignment($assignment, null, 1)->total();
        $batchLabels = $this->marksService->batchLabelsForAssignment($assignment);
        $stats = $this->progressStats($assignment, $totalStudents);

        $assignment->setAttribute('total_students', $totalStudents);
        $assignment->setAttribute('batch_labels', $batchLabels);
        $assignment->setAttribute('course_status', $this->courseStatusLabel($assignment));
        $assignment->setAttribute('credit_hours', $assignment->course?->credit ?? 0);
        $assignment->setAttribute('classes_taken', $stats['classes_taken']);
        $assignment->setAttribute('attendance_status', $stats['attendance_status']);
        $assignment->setAttribute('marks_entry_status', $stats['marks_entry_status']);
        $assignment->setAttribute('grade_submission_status', $stats['grade_submission_status']);
        $assignment->setAttribute('grade_submission_date', $stats['grade_submission_date']);
        $assignment->setAttribute('attendance_percentage', $stats['attendance_percentage']);
        $assignment->setAttribute('marks_entry_progress', $stats['marks_entry_progress']);
        $assignment->setAttribute('clo_assessment_progress', $stats['clo_assessment_progress']);
        $assignment->setAttribute('is_readonly', $this->isPreviousAssignment($assignment));

        return $assignment;
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardData(CourseAssignment $assignment): array
    {
        $assignment = $this->enrichAssignment($assignment, 'dashboard');
        $assignment->loadMissing(['teacher', 'academicSession', 'program', 'course', 'section', 'semester']);

        $totalStudents = (int) $assignment->getAttribute('total_students');
        $stats = $this->progressStats($assignment, $totalStudents);

        return [
            'assignment' => $assignment,
            'course_info' => [
                'academic_session' => $assignment->academicSession?->session_name,
                'academic_year' => $assignment->academicSession?->academic_year,
                'program' => $assignment->program?->program_name,
                'course_code' => $assignment->course?->course_code,
                'course_title' => $assignment->course?->course_title,
                'credit_hours' => $assignment->course?->credit,
                'batch' => implode(', ', $assignment->batch_labels ?? []),
                'section' => trim(($assignment->section?->section_code ?? '').' '.($assignment->section?->section_name ?? '')),
                'teacher_name' => $assignment->teacher?->teacher_name,
                'semester' => $assignment->semester?->semester_name,
            ],
            'quick_stats' => [
                'total_students' => $totalStudents,
                'total_classes' => $stats['total_classes'],
                'classes_taken' => $stats['classes_taken'],
                'attendance_percentage' => $stats['attendance_percentage'],
                'marks_entry_progress' => $stats['marks_entry_progress'],
                'clo_assessment_progress' => $stats['clo_assessment_progress'],
            ],
            'status' => [
                'attendance' => $stats['attendance_status'],
                'marks_entry' => $stats['marks_entry_status'],
                'grade_submission' => $stats['grade_submission_status'],
            ],
            'is_readonly' => $this->isPreviousAssignment($assignment),
            'is_current' => $this->isCurrentAssignment($assignment),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function progressStats(CourseAssignment $assignment, int $totalStudents): array
    {
        $classesTaken = $this->countAttendanceDocuments($assignment);
        $totalClasses = max($classesTaken, 0);

        $marksStats = $this->marksProgress($assignment, $totalStudents);
        $attendancePct = $this->estimateAttendancePercentage($assignment, $totalStudents);

        return [
            'total_classes' => $totalClasses,
            'classes_taken' => $classesTaken,
            'attendance_percentage' => $attendancePct,
            'attendance_status' => $this->statusFromProgress($attendancePct, 80),
            'marks_entry_progress' => $marksStats['progress'],
            'marks_entry_status' => $this->statusFromProgress($marksStats['progress'], 100),
            'grade_submission_status' => $marksStats['graded'] >= $totalStudents && $totalStudents > 0 ? 'Submitted' : ($marksStats['graded'] > 0 ? 'In Progress' : 'Pending'),
            'grade_submission_date' => $marksStats['last_updated'],
            'clo_assessment_progress' => $marksStats['progress'],
        ];
    }

    protected function countAttendanceDocuments(CourseAssignment $assignment): int
    {
        return (int) CourseFileDocument::query()
            ->whereHas('courseFile', fn (Builder $q) => $q->where('course_assignment_id', (int) $assignment->id))
            ->whereIn('document_type', [
                CourseFileDocumentTypes::ATTENDANCE_SHEET,
                CourseFileDocumentTypes::ATTENDANCE_REPORT,
            ])
            ->count();
    }

    /**
     * @return array{progress: float, graded: int, last_updated: ?string}
     */
    protected function marksProgress(CourseAssignment $assignment, int $totalStudents): array
    {
        if ($totalStudents === 0) {
            return ['progress' => 0, 'graded' => 0, 'last_updated' => null];
        }

        $studentIds = $this->marksService->studentsForAssignment($assignment, null, 5000)
            ->getCollection()
            ->pluck('id')
            ->all();

        if ($studentIds === []) {
            return ['progress' => 0, 'graded' => 0, 'last_updated' => null];
        }

        $rows = DB::table('student_marks')
            ->where('academic_session_id', (int) $assignment->academic_session_id)
            ->where('course_id', (int) $assignment->course_id)
            ->whereIn('student_id', $studentIds)
            ->select(['id', 'total_marks_grade_name', 'updated_at'])
            ->get();

        $withMarks = $rows->filter(fn ($r) => $r->total_marks_grade_name !== null && trim((string) $r->total_marks_grade_name) !== '')->count();
        $withAny = $rows->count();
        $progress = round(min(100, ($withAny / $totalStudents) * 100), 1);
        $lastUpdated = $rows->max('updated_at');

        return [
            'progress' => $progress,
            'graded' => $withMarks,
            'last_updated' => $lastUpdated ? (string) $lastUpdated : null,
        ];
    }

    protected function estimateAttendancePercentage(CourseAssignment $assignment, int $totalStudents): float
    {
        if ($totalStudents === 0) {
            return 0;
        }

        $columns = $this->marksService->markColumnsForAssignment($assignment);
        $attendanceCols = array_values(array_filter($columns, fn (string $c) => str_contains(strtolower($c), 'attendance')));

        if ($attendanceCols === []) {
            return 0;
        }

        $studentIds = $this->marksService->studentsForAssignment($assignment, null, 5000)
            ->getCollection()
            ->pluck('id')
            ->all();

        if ($studentIds === []) {
            return 0;
        }

        $select = array_merge(['student_id'], $attendanceCols);
        $rows = DB::table('student_marks')
            ->where('academic_session_id', (int) $assignment->academic_session_id)
            ->where('course_id', (int) $assignment->course_id)
            ->whereIn('student_id', $studentIds)
            ->get($select);

        if ($rows->isEmpty()) {
            return 0;
        }

        $sum = 0;
        $count = 0;
        foreach ($rows as $row) {
            foreach ($attendanceCols as $col) {
                $val = (float) ($row->{$col} ?? 0);
                if ($val > 0) {
                    $sum += $val;
                    $count++;
                }
            }
        }

        return $count > 0 ? round($sum / $count, 1) : 0;
    }

    protected function statusFromProgress(float $progress, float $completeAt): string
    {
        if ($progress >= $completeAt) {
            return 'Complete';
        }
        if ($progress > 0) {
            return 'In Progress';
        }

        return 'Pending';
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        $search = trim((string) $search);
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $q) use ($search) {
            $q->whereHas('course', function (Builder $sub) use ($search) {
                $sub->where('course_title', 'like', '%'.$search.'%')
                    ->orWhere('course_code', 'like', '%'.$search.'%');
            })->orWhereHas('program', function (Builder $sub) use ($search) {
                $sub->where('program_name', 'like', '%'.$search.'%')
                    ->orWhere('program_code', 'like', '%'.$search.'%');
            })->orWhereHas('academicSession', function (Builder $sub) use ($search) {
                $sub->where('session_name', 'like', '%'.$search.'%')
                    ->orWhere('academic_year', 'like', '%'.$search.'%');
            });
        });
    }

    protected function applySort(Builder $query, ?string $sort, string $direction): void
    {
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';
        $sort = trim((string) $sort);

        match ($sort) {
            'course_code' => $query->join('courses', 'courses.id', '=', 'course_assignments.course_id')
                ->orderBy('courses.course_code', $direction)
                ->select('course_assignments.*'),
            'course_title' => $query->join('courses', 'courses.id', '=', 'course_assignments.course_id')
                ->orderBy('courses.course_title', $direction)
                ->select('course_assignments.*'),
            'program' => $query->join('programs', 'programs.id', '=', 'course_assignments.program_id')
                ->orderBy('programs.program_name', $direction)
                ->select('course_assignments.*'),
            'session' => $query->join('academic_sessions', 'academic_sessions.id', '=', 'course_assignments.academic_session_id')
                ->orderBy('academic_sessions.session_name', $direction)
                ->select('course_assignments.*'),
            default => $query->orderBy('course_assignments.id', $direction),
        };
    }
}
