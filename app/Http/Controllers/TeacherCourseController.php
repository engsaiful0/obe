<?php

namespace App\Http\Controllers;

use App\Exports\AssignedCoursesExport;
use App\Models\CourseAssignment;
use App\Models\Teacher;
use App\Services\TeacherActivityLogger;
use App\Services\TeacherCourseMarksService;
use App\Services\TeacherCourseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TeacherCourseController extends Controller
{
    public function __construct(
        protected TeacherCourseService $courseService,
        protected TeacherCourseMarksService $marksService,
        protected TeacherActivityLogger $activityLogger,
    ) {}

    public function assigned(Request $request): View
    {
        $teacher = $this->resolveTeacherOrFail();
        $courses = $this->courseService->paginateForTeacher(
            $teacher,
            'assigned',
            $request->input('search'),
            $request->input('sort'),
            (string) $request->input('direction', 'desc'),
            (int) $request->input('per_page', 15)
        );

        $this->activityLogger->log('view_assigned_courses', 'Viewed assigned courses list');

        if ($request->ajax()) {
            return view('content.teacher-courses.partials.assigned-table', compact('courses'));
        }

        return view('content.teacher-courses.assigned', compact('courses'));
    }

    public function current(Request $request): View
    {
        $teacher = $this->resolveTeacherOrFail();
        $courses = $this->courseService->paginateForTeacher(
            $teacher,
            'current',
            $request->input('search'),
            $request->input('sort'),
            (string) $request->input('direction', 'desc'),
            (int) $request->input('per_page', 15)
        );

        $this->activityLogger->log('view_current_courses', 'Viewed current semester courses');

        if ($request->ajax()) {
            return view('content.teacher-courses.partials.current-table', compact('courses'));
        }

        return view('content.teacher-courses.current', compact('courses'));
    }

    public function previous(Request $request): View
    {
        $teacher = $this->resolveTeacherOrFail();
        $courses = $this->courseService->paginateForTeacher(
            $teacher,
            'previous',
            $request->input('search'),
            $request->input('sort'),
            (string) $request->input('direction', 'desc'),
            (int) $request->input('per_page', 15)
        );

        $this->activityLogger->log('view_previous_courses', 'Viewed previous semester courses');

        if ($request->ajax()) {
            return view('content.teacher-courses.partials.previous-table', compact('courses'));
        }

        return view('content.teacher-courses.previous', compact('courses'));
    }

    public function dashboard(CourseAssignment $courseAssignment, Request $request): View
    {
        Gate::authorize('view', $courseAssignment);
        $this->ensureTeacherOwnsOrAbort($courseAssignment);

        $dashboard = $this->courseService->dashboardData($courseAssignment);
        $tab = (string) $request->input('tab', 'overview');

        $this->activityLogger->log(
            'view_course_dashboard',
            'Opened course dashboard',
            $courseAssignment,
            ['tab' => $tab]
        );

        return view('content.teacher-courses.dashboard', [
            'dashboard' => $dashboard,
            'assignment' => $courseAssignment,
            'tab' => $tab,
        ]);
    }

    public function studentsPage(CourseAssignment $courseAssignment): View
    {
        Gate::authorize('view', $courseAssignment);
        $this->ensureTeacherOwnsOrAbort($courseAssignment);

        $dashboard = $this->courseService->dashboardData($courseAssignment);
        $this->activityLogger->log('view_student_list', 'Viewed student list', $courseAssignment);

        return view('content.teacher-courses.students', compact('courseAssignment', 'dashboard'));
    }

    public function attendancePage(CourseAssignment $courseAssignment): View
    {
        Gate::authorize('view', $courseAssignment);
        $this->ensureTeacherOwnsOrAbort($courseAssignment);

        $dashboard = $this->courseService->dashboardData($courseAssignment);
        $readonly = (bool) ($dashboard['is_readonly'] ?? false);

        $this->activityLogger->log('view_attendance', 'Viewed attendance module', $courseAssignment);

        return view('content.teacher-courses.attendance', compact('courseAssignment', 'dashboard', 'readonly'));
    }

    public function reportsPage(CourseAssignment $courseAssignment): View
    {
        Gate::authorize('view', $courseAssignment);
        $this->ensureTeacherOwnsOrAbort($courseAssignment);

        $dashboard = $this->courseService->dashboardData($courseAssignment);
        $this->activityLogger->log('view_reports', 'Viewed course reports', $courseAssignment);

        return view('content.teacher-courses.reports', compact('courseAssignment', 'dashboard'));
    }

    public function cloPage(CourseAssignment $courseAssignment): View
    {
        Gate::authorize('view', $courseAssignment);
        $this->ensureTeacherOwnsOrAbort($courseAssignment);

        $dashboard = $this->courseService->dashboardData($courseAssignment);
        $readonly = (bool) ($dashboard['is_readonly'] ?? false);

        return view('content.teacher-courses.clo-assessment', compact('courseAssignment', 'dashboard', 'readonly'));
    }

    public function ploPage(CourseAssignment $courseAssignment): View
    {
        Gate::authorize('view', $courseAssignment);
        $this->ensureTeacherOwnsOrAbort($courseAssignment);

        $dashboard = $this->courseService->dashboardData($courseAssignment);
        $readonly = (bool) ($dashboard['is_readonly'] ?? false);

        return view('content.teacher-courses.plo-assessment', compact('courseAssignment', 'dashboard', 'readonly'));
    }

    public function exportAssignedExcel(Request $request): BinaryFileResponse
    {
        $teacher = $this->resolveTeacherOrFail();
        $assignments = $this->courseService->allAssignedForExport($teacher, $request->input('search'));

        $this->activityLogger->log('export_assigned_excel', 'Exported assigned courses to Excel');

        return Excel::download(
            new AssignedCoursesExport($assignments),
            'assigned-courses-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function exportPreviousPdf(CourseAssignment $courseAssignment)
    {
        Gate::authorize('view', $courseAssignment);
        $this->ensureTeacherOwnsOrAbort($courseAssignment);

        $dashboard = $this->courseService->dashboardData($courseAssignment);
        $this->activityLogger->log('export_previous_pdf', 'Exported previous course summary PDF', $courseAssignment);

        $pdf = Pdf::loadView('content.teacher-courses.pdf.course-summary', [
            'dashboard' => $dashboard,
            'assignment' => $courseAssignment,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('course-'.$courseAssignment->id.'-summary.pdf');
    }

    public function studentsJson(CourseAssignment $courseAssignment, Request $request): JsonResponse
    {
        Gate::authorize('view', $courseAssignment);
        $this->ensureTeacherOwnsOrAbort($courseAssignment);

        $search = trim((string) $request->input('search', ''));
        $students = $this->marksService->studentsForAssignment($courseAssignment, $search, 5000);
        $existing = $this->marksService->existingMarksByStudent($courseAssignment, collect($students->items()));

        $rows = collect($students->items())->map(function ($student) use ($existing) {
            $marks = $existing[(int) $student->id] ?? [];

            return [
                'id' => (int) $student->id,
                'student_code' => (string) $student->student_code,
                'student_name' => (string) $student->student_name,
                'batch_name' => (string) ($student->batch?->batch_name ?? ''),
                'attendance_percentage' => isset($marks['total_marks_percentage']) ? (float) $marks['total_marks_percentage'] : null,
                'total_marks' => isset($marks['total_marks']) ? (float) $marks['total_marks'] : null,
                'grade' => $marks['total_marks_grade_name'] ?? null,
            ];
        })->values();

        return response()->json(['students' => $rows]);
    }

    private function resolveTeacherOrFail(): Teacher
    {
        $user = Auth::user();
        $ruleName = strtolower((string) ($user?->rule?->name ?? ''));
        abort_if($ruleName !== 'teacher', 403, 'Only teachers can access this module.');

        $teacher = $user?->teacher;
        abort_if(! $teacher, 403, 'Teacher profile is missing.');

        return $teacher;
    }

    private function ensureTeacherOwnsOrAbort(CourseAssignment $courseAssignment): void
    {
        $teacher = $this->resolveTeacherOrFail();
        abort_if((int) $courseAssignment->teacher_id !== (int) $teacher->id, 403, 'You can only access your assigned courses.');
    }
}
