<?php

namespace App\Services;

use App\Models\CourseAssignment;
use App\Models\StudentAttendance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentAttendanceService
{
    public function __construct(
        protected TeacherCourseMarksService $marksService,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function rosterForDate(CourseAssignment $assignment, string $date): array
    {
        $students = $this->marksService->allStudentsForAssignment($assignment);
        $existing = StudentAttendance::query()
            ->where('course_assignment_id', (int) $assignment->id)
            ->whereDate('attendance_date', $date)
            ->get()
            ->keyBy('student_id');

        return $students->values()->map(function ($student, int $index) use ($existing) {
            $record = $existing->get((int) $student->id);

            return [
                'serial' => $index + 1,
                'student_id' => (int) $student->id,
                'student_code' => (string) $student->student_code,
                'student_name' => (string) $student->student_name,
                'batch_name' => (string) ($student->batch?->batch_name ?? ''),
                'status' => $record?->status ?? StudentAttendance::STATUS_PRESENT,
                'remarks' => $record?->remarks,
                'saved' => $record !== null,
            ];
        })->all();
    }

    /**
     * @param  array<int, array{student_id:int, status:string, remarks?:?string}>  $rows
     * @return array{saved:int, date:string}
     */
    public function saveForDate(CourseAssignment $assignment, string $date, array $rows): array
    {
        $allowedIds = $this->marksService
            ->allowedStudentIds($assignment, collect($rows)->pluck('student_id')->map(fn ($id) => (int) $id)->all());

        if ($allowedIds === []) {
            throw ValidationException::withMessages([
                'rows' => [__('No valid students found for this course.')],
            ]);
        }

        $userId = Auth::id();
        $saved = 0;

        DB::transaction(function () use ($assignment, $date, $rows, $allowedIds, $userId, &$saved) {
            foreach ($rows as $row) {
                $studentId = (int) ($row['student_id'] ?? 0);
                if ($studentId <= 0 || ! in_array($studentId, $allowedIds, true)) {
                    continue;
                }

                $status = (string) ($row['status'] ?? StudentAttendance::STATUS_PRESENT);
                if (! in_array($status, [
                    StudentAttendance::STATUS_PRESENT,
                    StudentAttendance::STATUS_ABSENT,
                    StudentAttendance::STATUS_LATE,
                    StudentAttendance::STATUS_EXCUSED,
                ], true)) {
                    $status = StudentAttendance::STATUS_PRESENT;
                }

                StudentAttendance::query()->updateOrCreate(
                    [
                        'course_assignment_id' => (int) $assignment->id,
                        'student_id' => $studentId,
                        'attendance_date' => $date,
                    ],
                    [
                        'status' => $status,
                        'remarks' => $row['remarks'] ?? null,
                        'recorded_by' => $userId,
                    ]
                );
                $saved++;
            }
        });

        return ['saved' => $saved, 'date' => $date];
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(CourseAssignment $assignment): array
    {
        $totalStudents = $this->marksService->studentsForAssignment($assignment, null, 1)->total();
        $classDates = StudentAttendance::query()
            ->where('course_assignment_id', (int) $assignment->id)
            ->distinct()
            ->count('attendance_date');

        $records = StudentAttendance::query()
            ->where('course_assignment_id', (int) $assignment->id)
            ->get(['student_id', 'status']);

        $totalRecords = $records->count();
        $presentCount = $records->whereIn('status', [
            StudentAttendance::STATUS_PRESENT,
            StudentAttendance::STATUS_LATE,
            StudentAttendance::STATUS_EXCUSED,
        ])->count();

        $classAverage = $totalRecords > 0
            ? round(($presentCount / $totalRecords) * 100, 1)
            : 0;

        $perStudent = StudentAttendance::query()
            ->where('course_assignment_id', (int) $assignment->id)
            ->selectRaw("student_id, COUNT(*) as total, SUM(CASE WHEN status IN ('present','late','excused') THEN 1 ELSE 0 END) as present_count")
            ->groupBy('student_id')
            ->get()
            ->map(fn ($row) => [
                'student_id' => (int) $row->student_id,
                'percentage' => (int) $row->total > 0
                    ? round(((int) $row->present_count / (int) $row->total) * 100, 1)
                    : 0,
            ]);

        $studentsWithData = $perStudent->count();
        $avgStudentPct = $studentsWithData > 0
            ? round((float) $perStudent->avg('percentage'), 1)
            : 0;

        return [
            'total_students' => $totalStudents,
            'classes_taken' => $classDates,
            'total_classes' => $classDates,
            'attendance_percentage' => $avgStudentPct > 0 ? $avgStudentPct : $classAverage,
            'class_average' => $classAverage,
            'records_count' => $totalRecords,
        ];
    }

    /**
     * @return Collection<int, StudentAttendance>
     */
    public function history(CourseAssignment $assignment, ?string $from = null, ?string $to = null): Collection
    {
        $query = StudentAttendance::query()
            ->with(['student:id,student_code,student_name'])
            ->where('course_assignment_id', (int) $assignment->id);

        if ($from) {
            $query->whereDate('attendance_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('attendance_date', '<=', $to);
        }

        return $query->orderByDesc('attendance_date')->orderBy('student_id')->get();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function historyGroupedByDate(CourseAssignment $assignment, ?string $from = null, ?string $to = null): array
    {
        return $this->history($assignment, $from, $to)
            ->groupBy(fn (StudentAttendance $r) => $r->attendance_date->format('Y-m-d'))
            ->map(function (Collection $rows, string $date) {
                $present = $rows->whereIn('status', [
                    StudentAttendance::STATUS_PRESENT,
                    StudentAttendance::STATUS_LATE,
                    StudentAttendance::STATUS_EXCUSED,
                ])->count();

                return [
                    'date' => $date,
                    'total' => $rows->count(),
                    'present' => $present,
                    'absent' => $rows->where('status', StudentAttendance::STATUS_ABSENT)->count(),
                    'percentage' => $rows->count() > 0 ? round(($present / $rows->count()) * 100, 1) : 0,
                ];
            })
            ->values()
            ->all();
    }
}
