<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseAssignmentRequest;
use App\Http\Requests\UpdateCourseAssignmentRequest;
use App\Models\AcademicSession;
use App\Models\Batch;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Program;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Status;
use App\Models\RelatedTo;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = CourseAssignment::query()
            ->with([
                'academicSession:id,session_name',
                'program:id,program_name,program_code',
                'batch:id,batch_name,batch_code',
                'semester:id,semester_name',
                'course:id,course_code,course_title',
                'teacher:id,teacher_name,employee_id',
                'section:id,section_name,section_code',
            ]);

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('course', function ($cq) use ($q) {
                    $cq->where('course_code', 'like', "%{$q}%")
                        ->orWhere('course_title', 'like', "%{$q}%");
                })
                    ->orWhereHas('teacher', function ($tq) use ($q) {
                        $tq->where('teacher_name', 'like', "%{$q}%")
                            ->orWhere('employee_id', 'like', "%{$q}%");
                    })
                    ->orWhereHas('batch', function ($bq) use ($q) {
                        $bq->where('batch_name', 'like', "%{$q}%")
                            ->orWhere('batch_code', 'like', "%{$q}%");
                    })
                    ->orWhereHas('section', function ($sq) use ($q) {
                        $sq->where('section_name', 'like', "%{$q}%")
                            ->orWhere('section_code', 'like', "%{$q}%");
                    });
            });
        }

        foreach (['academic_session_id', 'program_id', 'batch_id', 'semester_id', 'section_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, (int) $request->input($field));
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $assignments = $query->latest('id')->paginate(15)->withQueryString();

        $sessions = AcademicSession::query()->orderByDesc('academic_year')->orderByDesc('session_name')->get(['id', 'session_name']);
        $programs = Program::query()->orderBy('program_name')->get(['id', 'program_name', 'program_code']);
        $batches = Batch::query()->orderBy('batch_name')->get(['id', 'batch_name', 'program_id']);
        $semesters = Semester::query()->orderBy('semester_order')->get(['id', 'semester_name', 'program_id']);
        $sections = Section::query()->orderBy('section_name')->get(['id', 'section_name', 'program_id']);

        return view('content.course-assignments.index', compact(
            'assignments',
            'sessions',
            'programs',
            'batches',
            'semesters',
            'sections'
        ));
    }

    public function create(): View
    {
        $courseAssignmentStatuses = Status::query()
            ->where('related_to_id', RelatedTo::where('name', 'OBE')->value('id'))
            ->orderBy('status_name')
            ->get(['id', 'status_name']);

        return view('content.course-assignments.create', array_merge($this->formLookups(), [
            'assignment' => null,
            'batches' => collect(),
            'semesters' => collect(),
            'courses' => collect(),
            'sections' => collect(),
            'courseAssignmentStatuses' => $courseAssignmentStatuses,
        ]));
    }

    private function formLookups(): array
    {
        $sessions = AcademicSession::query()->orderByDesc('academic_year')->orderByDesc('session_name')->get(['id', 'session_name']);
        $programs = Program::query()->orderBy('program_name')->get(['id', 'program_name', 'program_code']);
        $teachers = Teacher::query()->orderBy('teacher_name')->get(['id', 'teacher_name', 'employee_id']);

        return compact('sessions', 'programs', 'teachers');
    }

    private function dependentsForAssignment(CourseAssignment $assignment): array
    {
        $programId = $assignment->program_id;
        $batches = Batch::query()->where('program_id', $programId)->orderBy('batch_name')->get();
        $semesters = Semester::query()->where('program_id', $programId)->orderBy('semester_order')->get();
        $courses = Course::query()
            ->where('program_id', $programId)
            ->where('semester_id', $assignment->semester_id)
            ->orderBy('course_code')
            ->get();
        $sections = Section::query()
            ->where('program_id', $programId)
            ->where('batch_id', $assignment->batch_id)
            ->where('semester_id', $assignment->semester_id)
            ->orderBy('section_name')
            ->get();

        return compact('batches', 'semesters', 'courses', 'sections');
    }

    public function store(StoreCourseAssignmentRequest $request): RedirectResponse|JsonResponse
    {
        $assignment = CourseAssignment::create([
            'academic_session_id' => $request->academic_session_id,
            'program_id' => $request->program_id,
            'batch_id' => $request->batch_id,
            'semester_id' => $request->semester_id,
            'course_id' => $request->course_id,
            'teacher_id' => $request->teacher_id,
            'section_id' => $request->section_id,
            'status_id' => $request->status_id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('Course assignment created successfully.'),
                'redirect_url' => route('course-assignment.index'),
            ], 201);
        }

        return redirect()->route('course-assignment.index')->with('success', __('Course assignment created successfully.'));
    }

    public function show(CourseAssignment $course_assignment): View
    {
        $course_assignment->load([
            'academicSession',
            'program',
            'batch',
            'semester',
            'course',
            'teacher',
            'section',
        ]);

        return view('content.course-assignments.show', ['assignment' => $course_assignment]);
    }

    public function edit(CourseAssignment $course_assignment): View
    {
        $courseAssignmentStatuses = Status::query()
            ->where('related_to_id', RelatedTo::where('name', 'OBE')->value('id'))
            ->orderBy('status_name')
            ->get(['id', 'status_name']);
        return view('content.course-assignments.edit', array_merge(
            $this->formLookups(),
            $this->dependentsForAssignment($course_assignment),
            ['assignment' => $course_assignment, 'courseAssignmentStatuses' => $courseAssignmentStatuses ?? null]
        ));
    }

    public function update(UpdateCourseAssignmentRequest $request, CourseAssignment $course_assignment): RedirectResponse|JsonResponse
    {
        $course_assignment->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('Course assignment updated successfully.'),
                'redirect_url' => route('course-assignment.index'),
            ]);
        }

        return redirect()->route('course-assignment.index')->with('success', __('Course assignment updated successfully.'));
    }

    public function destroy(CourseAssignment $course_assignment): RedirectResponse
    {
        $course_assignment->delete();

        return redirect()->route('course-assignment.index')->with('success', __('Course assignment removed successfully.'));
    }
}
