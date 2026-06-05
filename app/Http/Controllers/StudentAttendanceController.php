<?php

namespace App\Http\Controllers;

use App\Models\CourseAssignment;
use App\Models\StudentAttendance;
use App\Services\StudentAttendanceService;
use App\Services\TeacherActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StudentAttendanceController extends Controller
{
    public function __construct(
        protected StudentAttendanceService $attendanceService,
        protected TeacherActivityLogger $activityLogger,
    ) {}

    public function roster(CourseAssignment $courseAssignment, Request $request): JsonResponse
    {
        Gate::authorize('view', $courseAssignment);
        $this->ensureAssignedTeacher($courseAssignment);

        $date = (string) $request->input('date', now()->toDateString());
        $request->validate(['date' => 'nullable|date']);

        return response()->json([
            'date' => $date,
            'readonly' => ! Gate::allows('manage', $courseAssignment),
            'students' => $this->attendanceService->rosterForDate($courseAssignment, $date),
        ]);
    }

    public function store(CourseAssignment $courseAssignment, Request $request): JsonResponse
    {
        Gate::authorize('manage', $courseAssignment);
        $this->ensureAssignedTeacher($courseAssignment);

        $validated = $request->validate([
            'attendance_date' => ['required', 'date'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.student_id' => ['required', 'integer'],
            'rows.*.status' => ['required', Rule::in([
                StudentAttendance::STATUS_PRESENT,
                StudentAttendance::STATUS_ABSENT,
                StudentAttendance::STATUS_LATE,
                StudentAttendance::STATUS_EXCUSED,
            ])],
            'rows.*.remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $this->attendanceService->saveForDate(
            $courseAssignment,
            $validated['attendance_date'],
            $validated['rows']
        );

        $this->activityLogger->log(
            'save_attendance',
            'Saved daily attendance for '.$validated['attendance_date'],
            $courseAssignment,
            ['saved' => $result['saved']]
        );

        return response()->json([
            'message' => __('Attendance saved for :count student(s).', ['count' => $result['saved']]),
            'saved' => $result['saved'],
            'date' => $result['date'],
        ]);
    }

    public function history(CourseAssignment $courseAssignment, Request $request): JsonResponse
    {
        Gate::authorize('view', $courseAssignment);
        $this->ensureAssignedTeacher($courseAssignment);

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        return response()->json([
            'summary' => $this->attendanceService->summary($courseAssignment),
            'dates' => $this->attendanceService->historyGroupedByDate(
                $courseAssignment,
                $validated['from'] ?? null,
                $validated['to'] ?? null
            ),
        ]);
    }

    public function summary(CourseAssignment $courseAssignment): JsonResponse
    {
        Gate::authorize('view', $courseAssignment);
        $this->ensureAssignedTeacher($courseAssignment);

        return response()->json($this->attendanceService->summary($courseAssignment));
    }

    private function ensureAssignedTeacher(CourseAssignment $courseAssignment): void
    {
        $user = auth()->user();
        $ruleName = strtolower((string) ($user?->rule?->name ?? ''));
        abort_if($ruleName !== 'teacher', 403);

        $teacherId = (int) ($user?->teacher?->id ?? 0);
        abort_if($teacherId <= 0 || $teacherId !== (int) $courseAssignment->teacher_id, 403);
    }
}
