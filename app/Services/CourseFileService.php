<?php

namespace App\Services;

use App\Models\AssessmentComponent;
use App\Models\Clo;
use App\Models\CloPoMapping;
use App\Models\CourseAssignment;
use App\Models\CourseFile;
use App\Models\CourseFileCqi;
use App\Models\CourseFileDocument;
use App\Models\ProgramOutcome;
use App\Models\QuestionCloMapping;
use App\Models\Status;
use App\Support\CourseFileDocumentTypes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CourseFileService
{
    public const CLO_TARGET_PERCENT = 60.0;

    public const PLO_TARGET_PERCENT = 60.0;

    public function __construct(
        protected TeacherCourseMarksService $marksService,
        protected StudentMarksGradeCalculator $gradeCalculator,
    ) {}

    public function getOrCreate(CourseAssignment $assignment): CourseFile
    {
        $assignment->loadMissing(['course', 'teacher', 'program', 'semester', 'academicSession', 'section']);

        return CourseFile::query()->firstOrCreate(
            ['course_assignment_id' => (int) $assignment->id],
            [
                'course_id' => (int) $assignment->course_id,
                'teacher_id' => (int) $assignment->teacher_id,
                'academic_session_id' => (int) $assignment->academic_session_id,
                'program_id' => (int) $assignment->program_id,
                'semester_id' => $assignment->semester_id,
                'section_id' => $assignment->section_id,
                'status' => 'draft',
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function buildPageData(CourseAssignment $assignment, bool $canManage): array
    {
        $assignment->load([
            'course',
            'program.department',
            'semester',
            'academicSession',
            'section',
            'teacher',
        ]);

        $courseFile = $this->getOrCreate($assignment);
        $courseFile->load(['documents.uploader', 'cqi']);

        $gradeReport = $this->marksService->buildGradeSheetReport($assignment);
        $summary = $gradeReport['summary'] ?? [];
        $totalStudents = (int) ($summary['total_students'] ?? 0);
        $summary['pass_rate'] = $totalStudents > 0
            ? round(((int) ($summary['passed_students'] ?? 0) / $totalStudents) * 100, 2)
            : 0;
        $gradeReport['summary'] = $summary;

        $students = $this->marksService->allStudentsForAssignment($assignment);
        $markColumns = $this->marksService->markFieldsForAssignment($assignment);
        $existingMarks = $this->marksService->existingMarksByStudent($assignment, $students);

        $checklist = $this->buildCompletionChecklist($courseFile, $gradeReport, $students->count());
        $completion = $this->calculateCompletionPercent($checklist);

        $courseFile->update([
            'completion_percentage' => $completion,
            'status' => $completion >= 100 ? 'complete' : ($completion > 0 ? 'in_progress' : 'draft'),
        ]);

        return [
            'courseFile' => $courseFile->fresh(['documents', 'cqi']),
            'courseInfo' => $this->courseInformation($assignment),
            'dashboard' => $this->dashboardCards($assignment, $courseFile, $gradeReport, $students->count()),
            'clos' => $this->cloSection($assignment),
            'assessments' => $this->assessmentPlan($assignment),
            'markDistribution' => $this->markDistribution($assignment, $markColumns, $existingMarks, $students->count()),
            'gradeReport' => $gradeReport,
            'marksRows' => $this->studentMarksRows($assignment, $students, $existingMarks, $markColumns),
            'cloAttainment' => $this->cloAttainment($assignment, $gradeReport),
            'ploAttainment' => $this->ploAttainment($assignment, $gradeReport),
            'courseAttainment' => $this->courseAttainmentReport($gradeReport, $summary),
            'documentsByType' => $courseFile->documents->groupBy('document_type'),
            'documentTypes' => CourseFileDocumentTypes::labels(),
            'documentSections' => CourseFileDocumentTypes::sections(),
            'checklist' => $checklist,
            'completionPercent' => $completion,
            'canManage' => $canManage,
            'batchLabels' => $this->marksService->batchLabelsForAssignment($assignment),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function courseInformation(CourseAssignment $assignment): array
    {
        $course = $assignment->course;

        return [
            'course_code' => (string) ($course?->course_code ?? '-'),
            'course_title' => (string) ($course?->course_title ?? '-'),
            'credit_hours' => $course?->credit ?? '-',
            'theory_lab' => (string) ($course?->course_type ?? '-'),
            'academic_session' => (string) ($assignment->academicSession?->session_name ?? '-'),
            'academic_year' => (string) ($assignment->academicSession?->academic_year ?? ''),
            'program' => (string) ($assignment->program?->program_name ?? '-'),
            'batch' => implode(', ', $this->marksService->batchLabelsForAssignment($assignment)) ?: '-',
            'section' => trim(($assignment->section?->section_code ?? '').' '.($assignment->section?->section_name ?? '')) ?: '-',
            'semester' => (string) ($assignment->semester?->semester_name ?? '-'),
            'instructor' => (string) ($assignment->teacher?->teacher_name ?? '-'),
        ];
    }

    /**
     * @return array<string, int|float|string>
     */
    private function dashboardCards(
        CourseAssignment $assignment,
        CourseFile $courseFile,
        array $gradeReport,
        int $totalStudents
    ): array {
        $summary = $gradeReport['summary'] ?? [];
        $cloCount = Clo::query()
            ->where('course_id', (int) $assignment->course_id)
            ->where('program_id', (int) $assignment->program_id)
            ->count();

        $assessmentCount = AssessmentComponent::query()
            ->where('course_id', (int) $assignment->course_id)
            ->whereHas('status', fn ($q) => $q->where('status_name', 'Active'))
            ->count();

        $cloAttainment = $this->cloAttainment($assignment, $gradeReport);
        $achievedClos = collect($cloAttainment['rows'] ?? [])->where('status', 'Achieved')->count();
        $totalClos = count($cloAttainment['rows'] ?? []);

        return [
            'total_students' => $totalStudents,
            'total_clos' => $cloCount,
            'total_assessments' => $assessmentCount,
            'average_marks' => round((float) ($summary['average_marks'] ?? 0), 2),
            'average_gpa' => round((float) ($summary['average_gpa'] ?? 0), 2),
            'pass_rate' => round((float) ($summary['pass_rate'] ?? 0), 2),
            'course_attainment' => $totalClos > 0
                ? round(($achievedClos / $totalClos) * 100, 2)
                : round((float) ($summary['average_percentage'] ?? 0), 2),
            'uploaded_documents' => $courseFile->documents()->count(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function cloSection(CourseAssignment $assignment): array
    {
        $approvedStatusIds = Status::query()
            ->whereIn('status_name', ['Active', 'Approved'])
            ->pluck('id')
            ->all();

        return Clo::query()
            ->with(['bloom:id,name', 'status:id,status_name', 'cloPoMappings.programOutcome:id,outcome_code,outcome_type,title'])
            ->where('course_id', (int) $assignment->course_id)
            ->where('program_id', (int) $assignment->program_id)
            ->orderBy('clo_code')
            ->get()
            ->map(function (Clo $clo) use ($approvedStatusIds) {
                $plos = $clo->cloPoMappings
                    ->map(fn (CloPoMapping $m) => ($m->programOutcome?->outcome_code ?? '').' — '.($m->programOutcome?->title ?? ''))
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'clo_code' => $clo->clo_code,
                    'statement' => $clo->title ?: $clo->description,
                    'bloom' => $clo->bloom?->name ?? '-',
                    'plo_mapping' => $plos !== [] ? implode('; ', $plos) : '-',
                    'status' => $clo->status?->status_name ?? '-',
                    'is_locked' => in_array((int) $clo->status_id, $approvedStatusIds, true),
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function assessmentPlan(CourseAssignment $assignment): array
    {
        return AssessmentComponent::query()
            ->with(['status:id,status_name', 'questionCloMappings.clo:id,clo_code'])
            ->where('course_id', (int) $assignment->course_id)
            ->where('program_id', (int) $assignment->program_id)
            ->whereHas('status', fn ($q) => $q->where('status_name', 'Active'))
            ->orderBy('component_type')
            ->orderBy('component_name')
            ->get()
            ->map(function (AssessmentComponent $component) {
                $clos = $component->questionCloMappings
                    ->map(fn (QuestionCloMapping $m) => $m->clo?->clo_code)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                return [
                    'name' => $component->component_name,
                    'type' => $component->component_type,
                    'weight' => (float) ($component->weight_percentage ?? 0),
                    'marks' => (float) ($component->marks ?? 0),
                    'related_clos' => $clos !== [] ? implode(', ', $clos) : '-',
                    'method' => $component->has_multiple_questions ? __('Question-based') : __('Direct marks'),
                ];
            })
            ->all();
    }

    /**
     * @param  array{columns: array, labels: array}  $markFields
     * @param  array<int, array<string, mixed>>  $existingMarks
     * @return array<int, array<string, mixed>>
     */
    public function markDistribution(
        CourseAssignment $assignment,
        array $markFields,
        array $existingMarks,
        int $studentCount
    ): array {
        $rows = [];
        foreach ($markFields['columns'] as $column) {
            $values = collect($existingMarks)
                ->pluck($column)
                ->map(fn ($v) => (float) $v)
                ->filter(fn ($v) => $v > 0);

            $rows[] = [
                'column' => $column,
                'label' => $markFields['labels'][$column] ?? $column,
                'average' => $values->isEmpty() ? 0 : round($values->avg(), 2),
                'max' => $values->isEmpty() ? 0 : round($values->max(), 2),
                'students_with_marks' => $values->count(),
                'total_students' => $studentCount,
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function studentMarksRows(
        CourseAssignment $assignment,
        Collection $students,
        array $existingMarks,
        array $markFields
    ): array {
        return $students->map(function ($student) use ($existingMarks, $markFields) {
            $marks = $existingMarks[(int) $student->id] ?? [];
            $assessmentMarks = [];
            foreach ($markFields['columns'] as $column) {
                $assessmentMarks[$column] = isset($marks[$column]) ? (float) $marks[$column] : null;
            }

            return [
                'student_code' => (string) $student->student_code,
                'student_name' => (string) $student->student_name,
                'assessment_marks' => $assessmentMarks,
                'total_marks' => (float) ($marks['total_marks'] ?? 0),
                'percentage' => (float) ($marks['total_marks_percentage'] ?? 0),
                'grade' => $marks['total_marks_grade_name'] ?? '-',
                'grade_point' => $marks['total_marks_grade_points'] ?? null,
            ];
        })->values()->all();
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, chart: array<string, array<int, mixed>>}
     */
    public function cloAttainment(CourseAssignment $assignment, array $gradeReport): array
    {
        $courseAvg = (float) ($gradeReport['summary']['average_percentage'] ?? 0);
        $clos = Clo::query()
            ->where('course_id', (int) $assignment->course_id)
            ->where('program_id', (int) $assignment->program_id)
            ->orderBy('clo_code')
            ->get();

        $rows = [];
        foreach ($clos as $clo) {
            $achieved = $this->calculateCloAchievedPercent($assignment, (int) $clo->id, $courseAvg);
            $rows[] = [
                'clo' => $clo->clo_code,
                'target' => self::CLO_TARGET_PERCENT,
                'achieved' => round($achieved, 2),
                'status' => $achieved >= self::CLO_TARGET_PERCENT ? 'Achieved' : 'Not Achieved',
            ];
        }

        return [
            'rows' => $rows,
            'chart' => [
                'labels' => collect($rows)->pluck('clo')->all(),
                'achieved' => collect($rows)->pluck('achieved')->all(),
                'target' => collect($rows)->pluck('target')->all(),
            ],
        ];
    }

    private function calculateCloAchievedPercent(CourseAssignment $assignment, int $cloId, float $fallback): float
    {
        $mappingIds = QuestionCloMapping::query()
            ->where('course_id', (int) $assignment->course_id)
            ->where('clo_id', $cloId)
            ->pluck('id')
            ->all();

        if ($mappingIds === []) {
            return $fallback;
        }

        $studentIds = $this->marksService->allStudentsForAssignment($assignment)->pluck('id')->all();
        if ($studentIds === []) {
            return 0;
        }

        $scores = DB::table('student_question_marks')
            ->join('student_marks', 'student_marks.id', '=', 'student_question_marks.student_mark_id')
            ->join('question_clo_mappings', 'question_clo_mappings.id', '=', 'student_question_marks.question_clo_mapping_id')
            ->whereIn('student_question_marks.question_clo_mapping_id', $mappingIds)
            ->whereIn('student_marks.student_id', $studentIds)
            ->where('student_marks.academic_session_id', (int) $assignment->academic_session_id)
            ->selectRaw('student_marks.student_id, SUM(student_question_marks.obtained_marks) as obtained, SUM(question_clo_mappings.marks) as total')
            ->groupBy('student_marks.student_id')
            ->get();

        if ($scores->isEmpty()) {
            return $fallback;
        }

        $passed = $scores->filter(function ($row) {
            $total = (float) $row->total;
            if ($total <= 0) {
                return false;
            }

            return ((float) $row->obtained / $total) * 100 >= self::CLO_TARGET_PERCENT;
        })->count();

        return ($passed / $scores->count()) * 100;
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, chart: array<string, array<int, mixed>>}
     */
    public function ploAttainment(CourseAssignment $assignment, array $gradeReport): array
    {
        $cloData = $this->cloAttainment($assignment, $gradeReport);
        $cloByCode = collect($cloData['rows'])->keyBy('clo');

        $plos = ProgramOutcome::query()
            ->where('program_id', (int) $assignment->program_id)
            ->whereIn('outcome_type', ['PLO', 'PO'])
            ->orderBy('outcome_code')
            ->get();

        $rows = [];
        foreach ($plos as $plo) {
            $mappings = CloPoMapping::query()
                ->with('clo:id,clo_code')
                ->where('program_outcome_id', $plo->id)
                ->where('course_id', (int) $assignment->course_id)
                ->get();

            if ($mappings->isEmpty()) {
                continue;
            }

            $weighted = 0;
            $weightSum = 0;
            foreach ($mappings as $mapping) {
                $code = $mapping->clo?->clo_code;
                $achieved = $code ? (float) ($cloByCode[$code]['achieved'] ?? 0) : 0;
                $level = max(1, (int) $mapping->mapping_level);
                $weighted += $achieved * $level;
                $weightSum += $level;
            }

            $achievedPct = $weightSum > 0 ? $weighted / $weightSum : 0;
            $rows[] = [
                'plo' => $plo->outcome_code,
                'title' => $plo->title,
                'target' => self::PLO_TARGET_PERCENT,
                'achieved' => round($achievedPct, 2),
            ];
        }

        return [
            'rows' => $rows,
            'chart' => [
                'labels' => collect($rows)->pluck('plo')->all(),
                'achieved' => collect($rows)->pluck('achieved')->all(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function courseAttainmentReport(array $gradeReport, array $summary): array
    {
        return [
            'course_average' => round((float) ($summary['average_percentage'] ?? 0), 2),
            'gpa_average' => round((float) ($summary['average_gpa'] ?? 0), 2),
            'pass_rate' => round((float) ($summary['pass_rate'] ?? 0), 2),
            'total_students' => (int) ($summary['total_students'] ?? 0),
            'passed_students' => (int) ($summary['passed_students'] ?? 0),
            'grade_distribution' => $gradeReport['grade_distribution'] ?? [],
        ];
    }

    /**
     * @return array<int, array{key: string, label: string, done: bool}>
     */
    public function buildCompletionChecklist(CourseFile $courseFile, array $gradeReport, int $studentCount): array
    {
        $docs = $courseFile->documents->pluck('document_type')->unique();
        $hasOutline = $docs->intersect(CourseFileDocumentTypes::sections()['course_outline'])->isNotEmpty();
        $hasPapers = $docs->intersect(CourseFileDocumentTypes::sections()['question_papers'])->isNotEmpty();
        $hasMaterials = $docs->intersect(CourseFileDocumentTypes::sections()['teaching_materials'])->isNotEmpty();
        $hasAttendance = $docs->intersect(CourseFileDocumentTypes::sections()['attendance'])->isNotEmpty();
        $cqi = $courseFile->cqi;
        $cqiDone = $cqi && trim((string) $cqi->strengths) !== '' && trim((string) $cqi->improvements) !== '';
        $hasMarks = ((float) ($gradeReport['summary']['average_marks'] ?? 0)) > 0 || $studentCount > 0;

        return [
            ['key' => 'outline', 'label' => __('Course Outline Uploaded'), 'done' => $hasOutline],
            ['key' => 'papers', 'label' => __('Question Papers Uploaded'), 'done' => $hasPapers],
            ['key' => 'marks', 'label' => __('Marks Submitted'), 'done' => $hasMarks],
            ['key' => 'clo', 'label' => __('CLO Attainment Generated'), 'done' => true],
            ['key' => 'materials', 'label' => __('Teaching Materials Uploaded'), 'done' => $hasMaterials],
            ['key' => 'attendance', 'label' => __('Attendance Records'), 'done' => $hasAttendance],
            ['key' => 'cqi', 'label' => __('CQI Completed'), 'done' => $cqiDone],
        ];
    }

    /**
     * @param  array<int, array{done: bool}>  $checklist
     */
    public function calculateCompletionPercent(array $checklist): float
    {
        if ($checklist === []) {
            return 0;
        }

        $done = collect($checklist)->where('done', true)->count();

        return round(($done / count($checklist)) * 100, 2);
    }

    public function recalculateCompletion(CourseFile $courseFile, CourseAssignment $assignment): void
    {
        $gradeReport = $this->marksService->buildGradeSheetReport($assignment);
        $students = $this->marksService->allStudentsForAssignment($assignment)->count();
        $courseFile->load(['documents', 'cqi']);
        $checklist = $this->buildCompletionChecklist($courseFile, $gradeReport, $students);
        $completion = $this->calculateCompletionPercent($checklist);

        $courseFile->update([
            'completion_percentage' => $completion,
            'status' => $completion >= 100 ? 'complete' : ($completion > 0 ? 'in_progress' : 'draft'),
        ]);
    }

    public function saveCqi(CourseFile $courseFile, array $data): CourseFileCqi
    {
        return CourseFileCqi::query()->updateOrCreate(
            ['course_file_id' => $courseFile->id],
            [
                'strengths' => $data['strengths'] ?? null,
                'weaknesses' => $data['weaknesses'] ?? null,
                'problems' => $data['problems'] ?? null,
                'improvements' => $data['improvements'] ?? null,
                'recommendations' => $data['recommendations'] ?? null,
            ]
        );
    }
}
