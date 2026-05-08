<?php

namespace App\Http\Controllers;

use App\Exports\StudentMarksTemplateExport;
use App\Http\Requests\TeacherCourseMarksImportRequest;
use App\Http\Requests\TeacherCourseMarksUpdateRequest;
use App\Imports\StudentMarksWorksheetImport;
use App\Models\CourseAssignment;
use App\Models\Teacher;
use App\Services\TeacherCourseMarksService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class MyCourseController extends Controller
{
    public function __construct(
        protected TeacherCourseMarksService $marksService
    ) {}

    public function courseList(Request $request): View
    {
        $teacher = $this->resolveTeacherOrFail();
        $search = trim((string) $request->input('search', ''));

        $query = CourseAssignment::query()
            ->with(['course:id,course_code,course_title', 'semester:id,semester_name', 'academicSession:id,session_name,academic_year'])
            ->where('teacher_id', (int) $teacher->id);

        if ($search !== '') {
            $query->whereHas('course', function ($sub) use ($search) {
                $sub->where('course_title', 'like', '%'.$search.'%')
                    ->orWhere('course_code', 'like', '%'.$search.'%');
            });
        }

        $courses = $query->latest('id')->paginate(10)->withQueryString();
        $courses->getCollection()->transform(function (CourseAssignment $assignment) {
            $assignment->setAttribute(
                'total_students',
                $this->marksService->studentsForAssignment($assignment, null, 1)->total()
            );

            return $assignment;
        });

        if ($request->ajax()) {
            return view('content.my-courses.partials.course-list-table', compact('courses'));
        }

        return view('content.my-courses.course-list', compact('courses'));
    }

    public function marksEntry(CourseAssignment $courseAssignment): View
    {
        Gate::authorize('view', $courseAssignment);

        $markColumns = $this->marksService->markColumns();
        $studentsPayload = ['students' => [], 'pagination' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 20, 'total' => 0]];
        $marksUnavailableMessage = null;

        try {
            $studentsPayload = $this->studentsResponsePayload($courseAssignment, null);
        } catch (Throwable $e) {
            $marksUnavailableMessage = $this->friendlyMarksMessage($e);
        }

        return view('content.my-courses.marks-entry', [
            'courseAssignment' => $courseAssignment->load(['course', 'semester', 'academicSession']),
            'markColumns' => $markColumns,
            'students' => $studentsPayload,
            'marksUnavailableMessage' => $marksUnavailableMessage,
        ]);
    }

    public function students(CourseAssignment $courseAssignment, Request $request): JsonResponse
    {
        Gate::authorize('view', $courseAssignment);

        $search = trim((string) $request->input('search', ''));
        try {
            return response()->json($this->studentsResponsePayload($courseAssignment, $search));
        } catch (Throwable $e) {
            return response()->json([
                'message' => $this->friendlyMarksMessage($e),
                'students' => [],
                'pagination' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 20, 'total' => 0],
            ], 422);
        }
    }

    public function saveMarks(CourseAssignment $courseAssignment, TeacherCourseMarksUpdateRequest $request): JsonResponse
    {
        Gate::authorize('view', $courseAssignment);

        $markColumns = $this->marksService->markColumns();
        $payload = $request->validated();
        $rows = collect($payload['students'] ?? [])->map(function (array $row) use ($markColumns) {
            $marks = [];
            foreach ($markColumns as $column) {
                $raw = $row['marks'][$column] ?? 0;
                $marks[$column] = is_numeric($raw) ? (float) $raw : 0;
            }

            return [
                'student_id' => (int) $row['student_id'],
                'marks' => $marks,
            ];
        })->all();

        try {
            $result = $this->marksService->saveMarks($courseAssignment, $rows);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $this->friendlyMarksMessage($e),
            ], 422);
        }

        return response()->json([
            'message' => 'Marks updated successfully.',
            'updated_rows' => $result['updated'],
        ]);
    }

    public function downloadTemplate(CourseAssignment $courseAssignment)
    {
        Gate::authorize('view', $courseAssignment);

        $markColumns = $this->marksService->markColumns();
        try {
            $students = collect($this->marksService->studentsForAssignment($courseAssignment, null, 10000)->items());
        } catch (Throwable $e) {
            return redirect()
                ->route('my-courses.marks-entry', $courseAssignment)
                ->with('error', $this->friendlyMarksMessage($e));
        }

        $headings = array_merge(['student_id', 'student_code', 'student_name'], $markColumns);
        $rows = $students->map(function ($student) use ($markColumns) {
            $row = [
                (int) $student->id,
                (string) $student->student_code,
                (string) $student->student_name,
            ];
            foreach ($markColumns as $column) {
                $row[] = '';
            }

            return $row;
        });

        return Excel::download(
            new StudentMarksTemplateExport($headings, $rows),
            'teacher_marks_template_'.$courseAssignment->id.'_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function importMarks(CourseAssignment $courseAssignment, TeacherCourseMarksImportRequest $request): JsonResponse
    {
        Gate::authorize('view', $courseAssignment);

        $reader = new StudentMarksWorksheetImport;
        Excel::import($reader, $request->file('file'));

        $sheet = $reader->rows;
        if ($sheet->isEmpty()) {
            return response()->json(['message' => 'The uploaded sheet is empty.'], 422);
        }

        $header = $sheet->shift()->map(fn ($cell) => trim((string) $cell))->values()->all();
        $markColumns = $this->marksService->markColumns();
        $requiredHeader = array_merge(['student_id', 'student_code', 'student_name'], $markColumns);

        foreach ($requiredHeader as $required) {
            if (! in_array($required, $header, true)) {
                return response()->json(['message' => "Missing required column: {$required}"], 422);
            }
        }

        $index = array_flip($header);
        $rows = [];
        $errors = [];

        foreach ($sheet as $rowIndex => $row) {
            $cells = $row->values()->all();
            $studentId = (int) ($cells[$index['student_id']] ?? 0);
            if ($studentId < 1) {
                continue;
            }

            $marks = [];
            foreach ($markColumns as $column) {
                $value = $cells[$index[$column]] ?? 0;
                if ($value === '' || $value === null) {
                    $marks[$column] = 0;
                    continue;
                }
                if (! is_numeric($value)) {
                    $errors[] = 'Row '.($rowIndex + 2)." has invalid value for {$column}.";
                    continue 2;
                }
                $marks[$column] = (float) $value;
            }

            $rows[] = [
                'student_id' => $studentId,
                'marks' => $marks,
            ];
        }

        if ($errors !== []) {
            return response()->json([
                'message' => 'Validation failed in uploaded file.',
                'errors' => $errors,
            ], 422);
        }

        $validator = Validator::make(['students' => $rows], [
            'students' => ['required', 'array', 'min:1'],
            'students.*.student_id' => ['required', 'integer', 'exists:students,id'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Uploaded data contains invalid student records.',
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        try {
            $result = $this->marksService->saveMarks($courseAssignment, $rows);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $this->friendlyMarksMessage($e),
            ], 422);
        }

        return response()->json([
            'message' => 'Excel marks imported successfully.',
            'updated_rows' => $result['updated'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function studentsResponsePayload(CourseAssignment $courseAssignment, ?string $search): array
    {
        $markColumns = $this->marksService->markColumns();
        $students = $this->marksService->studentsForAssignment($courseAssignment, $search, 20);
        $studentCollection = collect($students->items());
        $existing = $this->marksService->existingMarksByStudent($courseAssignment, $studentCollection);

        return [
            'students' => $studentCollection->map(function ($student) use ($existing, $markColumns) {
                $studentMarks = $existing[(int) $student->id] ?? [];
                $marks = [];
                foreach ($markColumns as $column) {
                    $marks[$column] = isset($studentMarks[$column]) ? (float) $studentMarks[$column] : 0;
                }

                return [
                    'id' => (int) $student->id,
                    'student_code' => (string) $student->student_code,
                    'student_name' => (string) $student->student_name,
                    'marks' => $marks,
                ];
            })->values()->all(),
            'pagination' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
            ],
        ];
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

    private function friendlyMarksMessage(Throwable $e): string
    {
        $raw = trim((string) $e->getMessage());
        if (stripos($raw, 'No assessment component found') !== false) {
            return __('Marks entry is not available for this course yet. Please create at least one assessment component first.');
        }

        if ($raw !== '') {
            return $raw;
        }

        return __('Could not load marks right now. Please try again.');
    }
}
