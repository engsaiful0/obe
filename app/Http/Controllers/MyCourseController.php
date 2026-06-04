<?php

namespace App\Http\Controllers;

use App\Exports\GradeSheetExport;
use App\Exports\StudentMarksTemplateExport;
use App\Http\Requests\TeacherCourseMarksImportRequest;
use App\Http\Requests\TeacherCourseMarksSingleRequest;
use App\Http\Requests\TeacherCourseMarksUpdateRequest;
use App\Imports\StudentMarksWorksheetImport;
use App\Models\AppSetting;
use App\Models\CourseAssignment;
use App\Models\Teacher;
use App\Services\StudentMarksGradeCalculator;
use App\Services\TeacherCourseMarksService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class MyCourseController extends Controller
{
    public function __construct(
        protected TeacherCourseMarksService $marksService,
        protected StudentMarksGradeCalculator $gradeCalculator,
    ) {}

    public function courseList(Request $request): View
    {
        $teacher = $this->resolveTeacherOrFail();
        $search = trim((string) $request->input('search', ''));

        $query = CourseAssignment::query()
            ->with([
                'course:id,course_code,course_title',
                'program:id,program_name,program_code',
                'semester:id,semester_name',
                'academicSession:id,session_name,academic_year',
                'section:id,section_code,section_name',
            ])
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
            $assignment->setAttribute(
                'batch_labels',
                $this->marksService->batchLabelsForAssignment($assignment)
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
        $maxMarks = $this->gradeCalculator->courseMaxMarks((int) $courseAssignment->course_id);

        try {
            $studentsPayload = $this->studentsResponsePayload($courseAssignment, null);
        } catch (Throwable $e) {
            $marksUnavailableMessage = $this->friendlyMarksMessage($e);
        }

        return view('content.my-courses.marks-entry', [
            'courseAssignment' => $courseAssignment->load(['course', 'program', 'semester', 'academicSession', 'section', 'teacher']),
            'markColumns' => $markColumns,
            'students' => $studentsPayload,
            'marksUnavailableMessage' => $marksUnavailableMessage,
            'maxMarks' => $maxMarks,
            'batchLabels' => $this->marksService->batchLabelsForAssignment($courseAssignment),
        ]);
    }

    public function gradeSheet(Request $request, CourseAssignment $courseAssignment): View
    {
        Gate::authorize('view', $courseAssignment);

        $studentId = $request->filled('student_id') ? (int) $request->input('student_id') : null;
        $report = $this->marksService->buildGradeSheetReport($courseAssignment, $studentId);
        $students = $this->marksService->studentsForAssignment($courseAssignment, null, 5000);

        return view('content.my-courses.grade-sheet', [
            'courseAssignment' => $courseAssignment,
            'report' => $report,
            'studentFilter' => $studentId,
            'studentOptions' => collect($students->items()),
        ]);
    }

    public function gradeSheetData(Request $request, CourseAssignment $courseAssignment): JsonResponse
    {
        Gate::authorize('view', $courseAssignment);

        $studentId = $request->filled('student_id') ? (int) $request->input('student_id') : null;
        $report = $this->marksService->buildGradeSheetReport($courseAssignment, $studentId);

        return response()->json($report);
    }

    public function gradeSheetPdf(Request $request, CourseAssignment $courseAssignment)
    {
        Gate::authorize('view', $courseAssignment);

        $studentId = $request->filled('student_id') ? (int) $request->input('student_id') : null;
        $report = $this->marksService->buildGradeSheetReport($courseAssignment, $studentId);
        $appSettings = AppSetting::query()->first();

        $pdf = Pdf::loadView('content.my-courses.grade-sheet-pdf', [
            'report' => $report,
            'appSettings' => $appSettings,
            'generatedAt' => now(),
        ]);
        $pdf->setPaper('A4', 'landscape');

        $filename = 'grade-sheet-'.$courseAssignment->id.'-'.now()->format('Ymd_His').'.pdf';

        return $pdf->download($filename);
    }

    public function gradeSheetPrint(Request $request, CourseAssignment $courseAssignment): View
    {
        Gate::authorize('view', $courseAssignment);

        $studentId = $request->filled('student_id') ? (int) $request->input('student_id') : null;
        $report = $this->marksService->buildGradeSheetReport($courseAssignment, $studentId);
        $appSettings = AppSetting::query()->first();

        return view('content.my-courses.grade-sheet-print', [
            'report' => $report,
            'appSettings' => $appSettings,
            'generatedAt' => now(),
        ]);
    }

    public function gradeSheetExcel(Request $request, CourseAssignment $courseAssignment): BinaryFileResponse
    {
        Gate::authorize('view', $courseAssignment);

        $studentId = $request->filled('student_id') ? (int) $request->input('student_id') : null;
        $report = $this->marksService->buildGradeSheetReport($courseAssignment, $studentId);

        return Excel::download(
            new GradeSheetExport($report['rows']),
            'grade-sheet-'.$courseAssignment->id.'-'.now()->format('Ymd_His').'.xlsx'
        );
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

        return $this->persistMarksResponse($courseAssignment, $this->normalizeMarkRows($request->validated()['students'] ?? []));
    }

    public function saveSingleMark(CourseAssignment $courseAssignment, TeacherCourseMarksSingleRequest $request): JsonResponse
    {
        Gate::authorize('view', $courseAssignment);

        $data = $request->validated();

        return $this->persistMarksResponse($courseAssignment, [[
            'student_id' => (int) $data['student_id'],
            'marks' => $data['marks'] ?? [],
        ]]);
    }

    public function downloadTemplate(CourseAssignment $courseAssignment)
    {
        Gate::authorize('view', $courseAssignment);

        $markColumns = $this->marksService->markColumns();
        try {
            $students = collect($this->marksService->studentsForAssignment($courseAssignment, null, 10000)->items());
            $existing = $this->marksService->existingMarksByStudent($courseAssignment, $students);
        } catch (Throwable $e) {
            return redirect()
                ->route('my-courses.marks-entry', $courseAssignment)
                ->with('error', $this->friendlyMarksMessage($e));
        }

        $headings = array_merge(['student_code'], $markColumns);
        $rows = $students->map(function ($student) use ($markColumns, $existing) {
            $studentMarks = $existing[(int) $student->id] ?? [];
            $row = [(string) $student->student_code];
            foreach ($markColumns as $column) {
                $row[] = isset($studentMarks[$column]) ? (float) $studentMarks[$column] : '';
            }

            return $row;
        });

        return Excel::download(
            new StudentMarksTemplateExport($headings, $rows),
            'teacher_marks_template_'.$courseAssignment->id.'_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function previewImport(CourseAssignment $courseAssignment, TeacherCourseMarksImportRequest $request): JsonResponse
    {
        Gate::authorize('view', $courseAssignment);

        try {
            $rows = $this->parseUploadedRows($courseAssignment, $request);
            $preview = $this->buildImportPreview($courseAssignment, $rows);

            return response()->json([
                'message' => __('Preview generated. Review the data before importing.'),
                'preview' => $preview,
                'rows' => $rows,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => __('Validation failed in uploaded file.'),
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $this->friendlyMarksMessage($e),
            ], 422);
        }
    }

    public function importMarks(CourseAssignment $courseAssignment, TeacherCourseMarksImportRequest $request): JsonResponse
    {
        Gate::authorize('view', $courseAssignment);

        try {
            $rows = $request->filled('confirmed_rows')
                ? json_decode((string) $request->input('confirmed_rows'), true, 512, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE)
                : $this->parseUploadedRows($courseAssignment, $request);

            if (! is_array($rows) || $rows === []) {
                return response()->json(['message' => __('No rows to import.')], 422);
            }

            $normalized = $this->normalizeMarkRows($rows);

            return $this->persistMarksResponse($courseAssignment, $normalized, __('Excel marks imported successfully.'));
        } catch (ValidationException $e) {
            return response()->json([
                'message' => __('Validation failed in uploaded file.'),
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $this->friendlyMarksMessage($e),
            ], 422);
        }
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
                    'registration_no' => (string) ($student->registration_no ?? ''),
                    'student_name' => (string) $student->student_name,
                    'batch_name' => (string) ($student->batch?->batch_name ?? ''),
                    'marks' => $marks,
                    'total_marks' => isset($studentMarks['total_marks']) ? (float) $studentMarks['total_marks'] : 0,
                    'total_marks_percentage' => isset($studentMarks['total_marks_percentage']) ? (float) $studentMarks['total_marks_percentage'] : 0,
                    'total_marks_grade_name' => $studentMarks['total_marks_grade_name'] ?? null,
                    'total_marks_grade_points' => isset($studentMarks['total_marks_grade_points']) ? (float) $studentMarks['total_marks_grade_points'] : null,
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

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array{student_id:int, marks:array<string, mixed>}>
     */
    private function normalizeMarkRows(array $rows): array
    {
        $markColumns = $this->marksService->markColumns();

        return collect($rows)->map(function (array $row) use ($markColumns) {
            $marks = [];
            foreach ($markColumns as $column) {
                $raw = $row['marks'][$column] ?? 0;
                $marks[$column] = is_numeric($raw) ? (float) $raw : 0;
            }

            return [
                'student_id' => (int) ($row['student_id'] ?? 0),
                'marks' => $marks,
            ];
        })->all();
    }

    /**
     * @param  array<int, array{student_id:int, marks:array<string, mixed>}>  $rows
     */
    private function persistMarksResponse(CourseAssignment $courseAssignment, array $rows, ?string $successMessage = null): JsonResponse
    {
        try {
            $result = $this->marksService->saveMarks($courseAssignment, $rows);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => __('Validation failed.'),
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $this->friendlyMarksMessage($e),
            ], 422);
        }

        return response()->json([
            'message' => $successMessage ?? __('Marks updated successfully.'),
            'updated_rows' => $result['updated'],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseUploadedRows(CourseAssignment $courseAssignment, TeacherCourseMarksImportRequest $request): array
    {
        $reader = new StudentMarksWorksheetImport;
        Excel::import($reader, $request->file('file'));

        $sheet = $reader->rows;
        if ($sheet->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => [__('The uploaded sheet is empty.')],
            ]);
        }

        $header = $sheet->shift()->map(fn ($cell) => trim((string) $cell))->values()->all();

        return $this->marksService->parseImportRows($courseAssignment, $header, $sheet);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function buildImportPreview(CourseAssignment $courseAssignment, array $rows): array
    {
        $courseId = (int) $courseAssignment->course_id;

        return collect($rows)->map(function (array $row) use ($courseId) {
            $computed = $this->gradeCalculator->buildPersistedMarks($row['marks'] ?? [], $courseId);

            return array_merge($row, [
                'total_marks' => $computed['total_marks'],
                'total_marks_percentage' => $computed['total_marks_percentage'],
                'total_marks_grade_name' => $computed['total_marks_grade_name'],
                'total_marks_grade_points' => $computed['total_marks_grade_points'],
            ]);
        })->values()->all();
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
        if ($e instanceof ValidationException) {
            $messages = collect($e->errors())->flatten()->filter()->values();
            if ($messages->isNotEmpty()) {
                return (string) $messages->first();
            }
        }

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
