<?php

namespace App\Http\Controllers;

use App\Exports\GradeSheetExport;
use App\Exports\StudentMarksTemplateExport;
use App\Http\Requests\TeacherCourseMarksBulkSaveRequest;
use App\Http\Requests\TeacherCourseMarksImportRequest;
use App\Http\Requests\TeacherCourseMarksSingleRequest;
use App\Http\Requests\TeacherCourseMarksUpdateRequest;
use App\Models\AppSetting;
use App\Support\SpreadsheetImportSupport;
use App\Services\MarksTemplateSpreadsheetReader;
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
        protected MarksTemplateSpreadsheetReader $spreadsheetReader,
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

        $markFields = ['columns' => [], 'labels' => [], 'max_by_column' => []];
        $studentsPayload = ['students' => [], 'pagination' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 5000, 'total' => 0]];
        $marksUnavailableMessage = null;
        $maxMarks = 100.0;

        try {
            $markFields = $this->marksService->markFieldsForAssignment($courseAssignment);
            $maxMarks = $this->gradeCalculator->courseMaxMarks((int) $courseAssignment->course_id);
            $studentsPayload = $this->studentsResponsePayload($courseAssignment, null);
        } catch (Throwable $e) {
            $marksUnavailableMessage = $this->friendlyMarksMessage($e);
        }

        $gradeScale = $this->gradeCalculator->gradeScale()
            ->map(fn ($g) => [
                'grade_name' => $g->grade_name,
                'from_marks' => (float) $g->from_marks,
                'to_marks' => (float) $g->to_marks,
            ])
            ->values()
            ->all();

        return view('content.my-courses.marks-entry', [
            'courseAssignment' => $courseAssignment->load(['course', 'program', 'semester', 'academicSession', 'section', 'teacher']),
            'markColumns' => $markFields['columns'],
            'markColumnLabels' => $markFields['labels'],
            'markColumnMax' => $markFields['max_by_column'],
            'students' => $studentsPayload,
            'marksUnavailableMessage' => $marksUnavailableMessage,
            'maxMarks' => $maxMarks,
            'gradeScale' => $gradeScale,
            'batchLabels' => $this->marksService->batchLabelsForAssignment($courseAssignment),
        ]);
    }

    public function importPage(CourseAssignment $courseAssignment): View
    {
        Gate::authorize('view', $courseAssignment);

        $markFields = ['columns' => [], 'labels' => []];
        $maxMarks = 100.0;
        $marksUnavailableMessage = null;

        try {
            $markFields = $this->marksService->markFieldsForAssignment($courseAssignment);
            $maxMarks = $this->gradeCalculator->courseMaxMarks((int) $courseAssignment->course_id);
            $this->marksService->allStudentsForAssignment($courseAssignment);
        } catch (Throwable $e) {
            $marksUnavailableMessage = $this->friendlyMarksMessage($e);
        }

        return view('content.my-courses.marks-import', [
            'courseAssignment' => $courseAssignment->load(['course', 'program', 'semester', 'academicSession', 'section', 'teacher']),
            'markColumns' => $markFields['columns'],
            'markColumnLabels' => $markFields['labels'],
            'maxMarks' => $maxMarks,
            'batchLabels' => $this->marksService->batchLabelsForAssignment($courseAssignment),
            'marksUnavailableMessage' => $marksUnavailableMessage,
            'excelImportReady' => SpreadsheetImportSupport::zipAvailable(),
            'importDiagnostics' => SpreadsheetImportSupport::diagnostics(),
        ]);
    }

    public function importCapabilities(CourseAssignment $courseAssignment): JsonResponse
    {
        Gate::authorize('view', $courseAssignment);

        return response()->json(SpreadsheetImportSupport::diagnostics());
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

        return $this->persistMarksResponse($courseAssignment, $this->normalizeMarkRows($request->validated()['students'] ?? [], $courseAssignment));
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

        $markColumns = $this->marksService->markColumnsForAssignment($courseAssignment);
        try {
            $students = $this->marksService->allStudentsForAssignment($courseAssignment);
            $existing = $this->marksService->existingMarksByStudent($courseAssignment, $students);
        } catch (Throwable $e) {
            return redirect()
                ->route('my-courses.marks-entry', $courseAssignment)
                ->with('error', $this->friendlyMarksMessage($e));
        }

        $headings = $this->marksService->excelTemplateHeadings($courseAssignment);
        $rows = $students->values()->map(function ($student) use ($markColumns, $existing) {
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
            [$header, $sheetRows] = $this->readUploadedSheet($request);
            $result = $this->marksService->parseImportSheetForPreview($courseAssignment, $header, $sheetRows);

            return response()->json([
                'message' => __('Preview generated. Review the data before saving.'),
                'preview' => $result['preview'],
                'rows' => $result['rows'],
                'summary' => $result['summary'],
                'mark_columns' => $this->marksService->markColumnsForAssignment($courseAssignment),
                'mark_column_labels' => $this->marksService->markFieldsForAssignment($courseAssignment)['labels'],
            ]);
        } catch (ValidationException $e) {
            $messages = collect($e->errors())->flatten()->filter()->values();

            return response()->json([
                'message' => $messages->isNotEmpty()
                    ? (string) $messages->first()
                    : __('Validation failed in uploaded file.'),
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $this->friendlyMarksMessage($e),
            ], 422);
        }
    }

    public function bulkSaveImport(CourseAssignment $courseAssignment, TeacherCourseMarksBulkSaveRequest $request): JsonResponse
    {
        Gate::authorize('view', $courseAssignment);

        $rows = $this->normalizeMarkRows($request->validated()['rows'] ?? [], $courseAssignment);

        return $this->persistMarksResponse($courseAssignment, $rows, __('Imported marks saved successfully.'));
    }

    public function importMarks(CourseAssignment $courseAssignment, Request $request): JsonResponse
    {
        Gate::authorize('view', $courseAssignment);

        if ($request->has('rows') && is_array($request->input('rows'))) {
            $validated = $request->validate((new TeacherCourseMarksBulkSaveRequest)->rules());

            return $this->persistMarksResponse(
                $courseAssignment,
                $this->normalizeMarkRows($validated['rows'], $courseAssignment),
                __('Imported marks saved successfully.')
            );
        }

        if ($request->filled('confirmed_rows')) {
            return $this->bulkSaveImportFromConfirmedJson($courseAssignment, $request);
        }

        return response()->json(['message' => __('No import data provided.')], 422);
    }

    /**
     * @return array<string, mixed>
     */
    private function studentsResponsePayload(CourseAssignment $courseAssignment, ?string $search): array
    {
        $markColumns = $this->marksService->markColumnsForAssignment($courseAssignment);
        $students = $this->marksService->studentsForAssignment($courseAssignment, $search, 5000);
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
    private function normalizeMarkRows(array $rows, ?CourseAssignment $courseAssignment = null): array
    {
        $markColumns = $courseAssignment !== null
            ? $this->marksService->markColumnsForAssignment($courseAssignment)
            : $this->marksService->markColumns();

        return collect($rows)->map(function (array $row) use ($markColumns) {
            $rawMarks = is_array($row['marks'] ?? null) ? $row['marks'] : [];
            $marks = [];
            foreach ($markColumns as $column) {
                $raw = $rawMarks[$column] ?? 0;
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
            'summary' => [
                'processed' => $result['processed'],
                'inserted' => $result['inserted'],
                'updated' => $result['updated'],
                'failed' => $result['failed'],
            ],
            'errors' => $result['errors'],
            'updated_rows' => $result['inserted'] + $result['updated'],
        ]);
    }

    private function bulkSaveImportFromConfirmedJson(CourseAssignment $courseAssignment, Request $request): JsonResponse
    {
        try {
            $decoded = json_decode((string) $request->input('confirmed_rows', '[]'), true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($decoded) || $decoded === []) {
                return response()->json(['message' => __('No rows to import.')], 422);
            }

            return $this->persistMarksResponse(
                $courseAssignment,
                $this->normalizeMarkRows($decoded, $courseAssignment),
                __('Imported marks saved successfully.')
            );
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
    }

    public function downloadTemplateCsv(CourseAssignment $courseAssignment)
    {
        Gate::authorize('view', $courseAssignment);

        $markColumns = $this->marksService->markColumnsForAssignment($courseAssignment);
        try {
            $students = $this->marksService->allStudentsForAssignment($courseAssignment);
            $existing = $this->marksService->existingMarksByStudent($courseAssignment, $students);
        } catch (Throwable $e) {
            return redirect()
                ->route('my-courses.import', $courseAssignment)
                ->with('error', $this->friendlyMarksMessage($e));
        }

        $headings = $this->marksService->excelTemplateHeadings($courseAssignment);
        $rows = $students->values()->map(function ($student) use ($markColumns, $existing) {
            $studentMarks = $existing[(int) $student->id] ?? [];
            $row = [(string) $student->student_code];
            foreach ($markColumns as $column) {
                $row[] = isset($studentMarks[$column]) ? (float) $studentMarks[$column] : '';
            }

            return $row;
        });

        $filename = 'teacher_marks_template_'.$courseAssignment->id.'_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($headings, $rows): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headings);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * @return array{0: array<int, string>, 1: \Illuminate\Support\Collection}
     */
    private function readUploadedSheet(TeacherCourseMarksImportRequest $request): array
    {
        return $this->spreadsheetReader->read($request->file('file'));
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
