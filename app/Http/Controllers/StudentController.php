<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\Batch;
use App\Models\BloodGroup;
use App\Models\Gender;
use App\Models\MaritalStatus;
use App\Models\Nationality;
use App\Models\Program;
use App\Models\Religion;
use App\Models\Rule;
use App\Models\Section;
use App\Models\Status;
use App\Models\RelatedTo;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule as ValidationRule;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    public const GUARDIAN_RELATIONS = [
        'Father', 'Mother', 'Legal Guardian', 'Brother', 'Sister', 'Uncle', 'Aunt',
        'Grandfather', 'Grandmother', 'Other',
    ];

    /** Lowercased status_name values on `statuses` (Related To = Batch) eligible for student admission. */
    private const BATCH_ELIGIBLE_STATUS_NAMES = ['active', 'running', 'actve'];

    public function index(Request $request)
    {
        $studentStatuses = Status::query()
        ->where('related_to_id', RelatedTo::where('name', 'Student')->value('id'))
        ->orderBy('status_name')
        ->get(['id', 'status_name']);

        $programs = Program::query()
            ->where('status', 'Active')
            ->orderBy('program_name')
            ->get(['id', 'program_name', 'program_code']);

        $academicSessions = AcademicSession::query()
            ->where('status', 'Active')
            ->orderByDesc('academic_year')
            ->orderBy('session_name')
            ->get(['id', 'session_name', 'academic_year']);

        $genders = Gender::query()->orderBy('gender_name')->get(['id', 'gender_name']);

        $religions = Religion::query()->orderBy('religion_name')->get(['id', 'religion_name']);

        return view('content.student.index', [
            'programs' => $programs,
            'academicSessions' => $academicSessions,
            'genders' => $genders,
            'religions' => $religions,
            'studentStatuses' => $studentStatuses,
            'sections' => Section::query()
                ->orderBy('section_name')
                ->get(['id', 'section_name', 'section_code', 'batch_id']),
        ]);
    }

    /**
     * Paginated student list JSON for AJAX (default 40 per page).
     */
    public function listPaginated(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 40);
        if (! in_array($perPage, [10, 20, 40, 50, 100], true)) {
            $perPage = 40;
        }

        $sort = $request->input('sort', 'student_name');
        $allowedSort = ['student_name', 'student_code', 'created_at', 'admission_date', 'id'];
        if (! in_array($sort, $allowedSort, true)) {
            $sort = 'student_name';
        }

        $dir = strtolower((string) $request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $query = Student::query()
            ->with([
                'program:id,program_name,program_code',
                'batch:id,batch_name,batch_code',
                'academicSession:id,session_name,academic_year',
                'gender:id,gender_name',
                'religion:id,religion_name',
            ]);

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->input('q')).'%';
            $query->where(function ($w) use ($term) {
                $w->where('students.student_name', 'like', $term)
                    ->orWhere('students.student_code', 'like', $term)
                    ->orWhere('students.email', 'like', $term)
                    ->orWhere('students.phone', 'like', $term)
                    ->orWhere('students.father_name', 'like', $term)
                    ->orWhere('students.mother_name', 'like', $term)
                    ->orWhere('students.guardian_name', 'like', $term);
            });
        }

        if ($request->filled('program_id')) {
            $query->where('students.program_id', (int) $request->input('program_id'));
        }

        if ($request->filled('batch_id')) {
            $query->where('students.batch_id', (int) $request->input('batch_id'));
        }

        if ($request->filled('section_id')) {
            $sectionId = (int) $request->input('section_id');
            $section = Section::query()->find($sectionId);
            if ($section) {
                $code = trim((string) ($section->section_code ?? ''));
                $name = trim((string) ($section->section_name ?? ''));
                $query->where(function ($w) use ($code, $name) {
                    if ($code !== '') {
                        $w->where('students.section', $code);
                    }
                    if ($name !== '') {
                        $w->orWhere('students.section', $name);
                    }
                });
            }
        }

        if ($request->filled('academic_session_id')) {
            $query->where('students.academic_session_id', (int) $request->input('academic_session_id'));
        }

        if ($request->filled('gender_id')) {
            $query->where('students.gender_id', (int) $request->input('gender_id'));
        }

        if ($request->filled('religion_id')) {
            $query->where('students.religion_id', (int) $request->input('religion_id'));
        }

        if ($request->filled('status_id')) {
            $query->where('students.status_id', (int) $request->input('status_id'));
        }

        if ($request->filled('shift')) {
            $shift = $request->input('shift');
            if (in_array($shift, ['Morning', 'Evening', 'Weekend'], true)) {
                $query->where('students.shift', $shift);
            }
        }

        if ($request->filled('student_type')) {
            $t = $request->input('student_type');
            if (in_array($t, ['Regular', 'Transfer', 'Foreign'], true)) {
                $query->where('students.student_type', $t);
            }
        }

        if ($request->filled('admission_date_from')) {
            $query->whereDate('students.admission_date', '>=', $request->input('admission_date_from'));
        }

        if ($request->filled('admission_date_to')) {
            $query->whereDate('students.admission_date', '<=', $request->input('admission_date_to'));
        }

        $paginator = $query
            ->orderBy($sort, $dir)
            ->paginate($perPage)
            ->appends($request->except('page'));

        $data = collect($paginator->items())->map(function (Student $s) {
            return [
                'id' => $s->id,
                'student_code' => $s->student_code,
                'student_name' => $s->student_name,
                'picture_url' => $s->picture ? Storage::disk('public')->url($s->picture) : null,
                'email' => $s->email,
                'phone' => $s->phone,
                'status' => $s->status->status_name,
                'shift' => $s->shift,
                'student_type' => $s->student_type,
                'program' => $s->program,
                'batch' => $s->batch,
                'section' => $s->section,
                'academic_session' => $s->academicSession,
                'gender' => $s->gender,
                'religion' => $s->religion,
                'admission_date' => $s->admission_date?->format('Y-m-d'),
                'actions' => [
                    'show_url' => route('student.show', $s->id),
                    'edit_url' => route('student.edit', $s->id),
                    'delete_url' => route('student.destroy', $s->id),
                ],
            ];
        })->values();

        return response()->json([  
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'links' => [
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }

    public function create()
    {
        $studentStatuses = Status::query()
            ->where('related_to_id', RelatedTo::where('name', 'Student')->value('id'))
            ->orderBy('status_name')
            ->get(['id', 'status_name']);

        return view('content.student.create', [
            'guardian_relations' => self::GUARDIAN_RELATIONS,
            'studentStatuses' => $studentStatuses,
        ]);
    }

    public function createMeta(Request $request)
    {
        $programs = Program::query()
            ->where('status', 'Active')
            ->orderBy('program_name')
            ->get(['id', 'program_name', 'program_code']);

        $academicSessions = AcademicSession::query()
            ->where('status', 'Active')
            ->orderByDesc('academic_year')
            ->orderBy('session_name')
            ->get(['id', 'session_name', 'academic_year']);

        $genders = Gender::query()->orderBy('gender_name')->get(['id', 'gender_name']);

        $religions = Religion::query()->orderBy('religion_name')->get(['id', 'religion_name']);

        $nationalities = Nationality::query()->orderBy('nationality_name')->get(['id', 'nationality_name']);

        $bloodGroups = BloodGroup::query()->orderBy('blood_group_name')->get(['id', 'blood_group_name']);

        $maritalStatuses = MaritalStatus::query()->orderBy('marital_status_name')->get(['id', 'marital_status_name']);

        return response()->json([
            'programs' => $programs,
            'academic_sessions' => $academicSessions,
            'genders' => $genders,
            'religions' => $religions,
            'nationalities' => $nationalities,
            'blood_groups' => $bloodGroups,
            'marital_statuses' => $maritalStatuses,
        ]);
    }

    public function batchesByProgram(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
        ]);

        if ($request->boolean('all_for_program')) {
            $batches = Batch::query()
                ->where('program_id', $request->program_id)
                ->with(['academicSession:id,session_name'])
                ->orderBy('batch_name')
                ->get(['id', 'program_id', 'batch_name', 'batch_code', 'academic_session_id']);

            return response()->json(['data' => $batches]);
        }

        // Admission / create form: batches linked to Batch-related status (active / running)
        $placeholders = implode(',', array_fill(0, count(self::BATCH_ELIGIBLE_STATUS_NAMES), '?'));

        $batches = Batch::query()
            ->join('statuses', 'statuses.id', '=', 'batches.status_id')
            ->join('related_tos', 'related_tos.id', '=', 'statuses.related_to_id')
            ->where('batches.program_id', $request->program_id)
            ->whereRaw('LOWER(TRIM(related_tos.name)) = ?', ['batch'])
            ->whereRaw(
                'LOWER(TRIM(statuses.status_name)) IN ('.$placeholders.')',
                self::BATCH_ELIGIBLE_STATUS_NAMES
            )
            ->with(['academicSession:id,session_name'])
            ->orderBy('batches.batch_name')
            ->select([
                'batches.id',
                'batches.program_id',
                'batches.batch_name',
                'batches.batch_code',
                'batches.academic_session_id',
            ])
            ->get();

        return response()->json(['data' => $batches]);
    }

    public function store(Request $request)
    {
        $this->normalizeOptionalLogin($request);
        $this->normalizeEmptyOptionals($request);

        $rules = [
            'program_id' => 'required|exists:programs,id',
            'batch_id' => 'required|exists:batches,id',
            'student_code' => 'required|string|max:100|unique:students,student_code',
            'student_name' => 'required|string|max:255',
            'picture' => 'nullable|image|max:4096',
            'father_name' => 'required|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'present_address' => 'nullable|string|max:2000',
            'permanent_address' => 'nullable|string|max:2000',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'gender_id' => 'required|exists:genders,id',
            'religion_id' => 'required|exists:religions,id',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'status_id' => 'required|exists:statuses,id',
            'date_of_birth' => 'nullable|date',
            'nationality_id' => 'nullable|exists:nationalities,id',
            'nid_or_birth_cert_no' => 'nullable|string|max:120',
            'blood_group_id' => 'nullable|exists:blood_groups,id',
            'marital_status_id' => 'nullable|exists:marital_statuses,id',
            'admission_date' => 'nullable|date',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_relation' => ['nullable', 'string', ValidationRule::in(self::GUARDIAN_RELATIONS)],
            'guardian_phone' => 'nullable|string|max:30',
            'guardian_email' => 'nullable|email|max:255',
            'guardian_address' => 'nullable|string|max:2000',
            'shift' => 'required|in:Morning,Evening',
            'student_type' => 'required|in:Regular,Transfer,Foreign',
            'signature' => 'nullable|image|max:4096',
            'nid_document' => 'nullable|file|mimes:jpeg,jpg,png,webp,pdf|max:5120',
            'login_email' => [
                'nullable',
                'email',
                'max:255',
                ValidationRule::unique('users', 'email'),
            ],
            'password' => 'nullable|string|min:6|max:255',
        ];

        if ($request->filled('login_email')) {
            $rules['password'] = 'required|string|min:6|max:255';
        }

        $validated = $request->validate($rules);

        $batchMatchesProgram = Batch::where('id', $request->batch_id)
            ->where('program_id', $request->program_id)
            ->exists();
        if (! $batchMatchesProgram) {
            throw ValidationException::withMessages([
                'batch_id' => 'The selected batch does not belong to the chosen program.',
            ]);
        }

        return DB::transaction(function () use ($request, $validated) {
            $picturePath = null;
            if ($request->hasFile('picture')) {
                $picturePath = $request->file('picture')->store('student-pictures', 'public');
            }

            $signaturePath = null;
            if ($request->hasFile('signature')) {
                $signaturePath = $request->file('signature')->store('student-signatures', 'public');
            }

            $nidDocPath = null;
            if ($request->hasFile('nid_document')) {
                $nidDocPath = $request->file('nid_document')->store('student-nid-documents', 'public');
            }

            $userId = null;
            if ($request->filled('login_email')) {
                $ruleId = Rule::where('name', 'Student')->value('id');
                if (! $ruleId) {
                    abort(422, 'The Student role is missing from the rules table. Run database seeders.');
                }
                $userId = User::create([
                    'name' => $request->student_name,
                    'email' => $request->login_email,
                    'password' => $request->password,
                    'rule_id' => $ruleId,
                ])->id;
            }

            $student = Student::create([
                'program_id' => $validated['program_id'],
                'batch_id' => $validated['batch_id'],
                'student_code' => $validated['student_code'],
                'student_name' => $validated['student_name'],
                'picture' => $picturePath,
                'father_name' => $validated['father_name'],
                'mother_name' => $request->input('mother_name'),
                'present_address' => $request->input('present_address'),
                'permanent_address' => $request->input('permanent_address'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'gender_id' => $validated['gender_id'],
                'religion_id' => $validated['religion_id'],
                'academic_session_id' => $validated['academic_session_id'],
                'user_id' => $userId,
                'status_id' => $validated['status_id'],
                'date_of_birth' => $request->input('date_of_birth'),
                'nationality_id' => $request->input('nationality_id'),
                'nid_or_birth_cert_no' => $request->input('nid_or_birth_cert_no'),
                'blood_group_id' => $request->input('blood_group_id'),
                'marital_status_id' => $request->input('marital_status_id'),
                'admission_date' => $request->input('admission_date'),
                'guardian_name' => $request->input('guardian_name'),
                'guardian_relation' => $request->input('guardian_relation'),
                'guardian_phone' => $request->input('guardian_phone'),
                'guardian_email' => $request->input('guardian_email'),
                'guardian_address' => $request->input('guardian_address'),
                'shift' => $validated['shift'],
                'student_type' => $validated['student_type'],
                'signature' => $signaturePath,
                'nid_document' => $nidDocPath,
            ]);

            $student->load([
                'program:id,program_name,program_code',
                'batch:id,batch_name,batch_code',
                'gender:id,gender_name',
                'religion:id,religion_name',
                'academicSession:id,session_name',
                'nationality:id,nationality_name',
                'bloodGroup:id,blood_group_name',
                'maritalStatus:id,marital_status_name',
            ]);

            return response()->json([
                'message' => 'Student created successfully.',
                'student' => $student,
            ], Response::HTTP_CREATED);
        });
    }

    public function show(Student $student)
    {
        $student->load([
            'program:id,program_name,program_code',
            'batch:id,batch_name,batch_code',
            'gender:id,gender_name',
            'religion:id,religion_name',
            'academicSession:id,session_name,academic_year',
            'nationality:id,nationality_name',
            'bloodGroup:id,blood_group_name',
            'maritalStatus:id,marital_status_name',
            'status:id,status_name',
        ]);

        return view('content.student.show-student', compact('student'));
    }

    public function edit(Student $student)
    {
        $student->load('user:id,email');

        $studentStatuses = Status::query()
            ->where('related_to_id', RelatedTo::where('name', 'Student')->value('id'))
            ->orderBy('status_name')
            ->get(['id', 'status_name']);

        $programs = Program::query()->where('status', 'Active')->orderBy('program_name')->get(['id', 'program_name', 'program_code']);
        $batches = Batch::query()->where('program_id', $student->program_id)->orderBy('batch_name')->get(['id', 'batch_name', 'batch_code']);
        $academicSessions = AcademicSession::query()->where('status', 'Active')->orderByDesc('academic_year')->orderBy('session_name')->get(['id', 'session_name', 'academic_year']);
        $genders = Gender::query()->orderBy('gender_name')->get(['id', 'gender_name']);
        $religions = Religion::query()->orderBy('religion_name')->get(['id', 'religion_name']);
        $nationalities = Nationality::query()->orderBy('nationality_name')->get(['id', 'nationality_name']);
        $bloodGroups = BloodGroup::query()->orderBy('blood_group_name')->get(['id', 'blood_group_name']);
        $maritalStatuses = MaritalStatus::query()->orderBy('marital_status_name')->get(['id', 'marital_status_name']);

        return view('content.student.edit-student', [
            'student' => $student,
            'studentStatuses' => $studentStatuses,
            'programs' => $programs,
            'batches' => $batches,
            'academicSessions' => $academicSessions,
            'genders' => $genders,
            'religions' => $religions,
            'nationalities' => $nationalities,
            'bloodGroups' => $bloodGroups,
            'maritalStatuses' => $maritalStatuses,
            'guardian_relations' => self::GUARDIAN_RELATIONS,
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $this->normalizeOptionalLogin($request);
        $this->normalizeEmptyOptionals($request);

        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'batch_id' => 'required|exists:batches,id',
            'student_code' => 'required|string|max:100|unique:students,student_code,'.$student->id,
            'student_name' => 'required|string|max:255',
            'picture' => 'nullable|image|max:4096',
            'father_name' => 'required|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'present_address' => 'nullable|string|max:2000',
            'permanent_address' => 'nullable|string|max:2000',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'gender_id' => 'required|exists:genders,id',
            'religion_id' => 'required|exists:religions,id',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'status_id' => 'required|exists:statuses,id',
            'date_of_birth' => 'nullable|date',
            'nationality_id' => 'nullable|exists:nationalities,id',
            'nid_or_birth_cert_no' => 'nullable|string|max:120',
            'blood_group_id' => 'nullable|exists:blood_groups,id',
            'marital_status_id' => 'nullable|exists:marital_statuses,id',
            'admission_date' => 'nullable|date',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_relation' => ['nullable', 'string', ValidationRule::in(self::GUARDIAN_RELATIONS)],
            'guardian_phone' => 'nullable|string|max:30',
            'guardian_email' => 'nullable|email|max:255',
            'guardian_address' => 'nullable|string|max:2000',
            'shift' => 'required|in:Morning,Evening,Weekend',
            'student_type' => 'required|in:Regular,Transfer,Foreign',
            'signature' => 'nullable|image|max:4096',
            'nid_document' => 'nullable|file|mimes:jpeg,jpg,png,webp,pdf|max:5120',
            'login_email' => [
                'nullable',
                'email',
                'max:255',
                ValidationRule::unique('users', 'email')->ignore($student->user_id),
            ],
            'password' => 'nullable|string|min:6|max:255',
        ]);

        if ($request->filled('login_email') && ! $student->user_id && ! $request->filled('password')) {
            throw ValidationException::withMessages([
                'password' => 'Password is required when creating a portal login.',
            ]);
        }

        $batchMatchesProgram = Batch::where('id', $request->batch_id)
            ->where('program_id', $request->program_id)
            ->exists();
        if (! $batchMatchesProgram) {
            throw ValidationException::withMessages([
                'batch_id' => 'The selected batch does not belong to the chosen program.',
            ]);
        }

        DB::transaction(function () use ($request, $validated, $student) {
            $picturePath = $student->picture;
            if ($request->hasFile('picture')) {
                $picturePath = $request->file('picture')->store('student-pictures', 'public');
            }

            $signaturePath = $student->signature;
            if ($request->hasFile('signature')) {
                $signaturePath = $request->file('signature')->store('student-signatures', 'public');
            }

            $nidDocPath = $student->nid_document;
            if ($request->hasFile('nid_document')) {
                $nidDocPath = $request->file('nid_document')->store('student-nid-documents', 'public');
            }

            $userId = $student->user_id;
            if ($request->filled('login_email')) {
                if ($userId) {
                    $userData = [
                        'name' => $validated['student_name'],
                        'email' => $request->input('login_email'),
                    ];
                    if ($request->filled('password')) {
                        $userData['password'] = $request->input('password');
                    }
                    User::where('id', $userId)->update($userData);
                } else {
                    $ruleId = Rule::where('name', 'Student')->value('id');
                    if (! $ruleId) {
                        abort(422, 'The Student role is missing from the rules table. Run database seeders.');
                    }
                    $userId = User::create([
                        'name' => $validated['student_name'],
                        'email' => $request->input('login_email'),
                        'password' => $request->input('password'),
                        'rule_id' => $ruleId,
                    ])->id;
                }
            }

            $student->update([
                'program_id' => $validated['program_id'],
                'batch_id' => $validated['batch_id'],
                'student_code' => $validated['student_code'],
                'student_name' => $validated['student_name'],
                'picture' => $picturePath,
                'father_name' => $validated['father_name'],
                'mother_name' => $request->input('mother_name'),
                'present_address' => $request->input('present_address'),
                'permanent_address' => $request->input('permanent_address'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'gender_id' => $validated['gender_id'],
                'religion_id' => $validated['religion_id'],
                'academic_session_id' => $validated['academic_session_id'],
                'status_id' => $validated['status_id'],
                'date_of_birth' => $request->input('date_of_birth'),
                'nationality_id' => $request->input('nationality_id'),
                'nid_or_birth_cert_no' => $request->input('nid_or_birth_cert_no'),
                'blood_group_id' => $request->input('blood_group_id'),
                'marital_status_id' => $request->input('marital_status_id'),
                'admission_date' => $request->input('admission_date'),
                'guardian_name' => $request->input('guardian_name'),
                'guardian_relation' => $request->input('guardian_relation'),
                'guardian_phone' => $request->input('guardian_phone'),
                'guardian_email' => $request->input('guardian_email'),
                'guardian_address' => $request->input('guardian_address'),
                'shift' => $validated['shift'],
                'student_type' => $validated['student_type'],
                'signature' => $signaturePath,
                'nid_document' => $nidDocPath,
                'user_id' => $userId,
            ]);
        });

        return redirect()
            ->route('student.view-student')
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student): JsonResponse
    {
        $student->delete();

        return response()->json([
            'message' => 'Student deleted successfully.',
        ]);
    }

    private function normalizeOptionalLogin(Request $request): void
    {
        $v = $request->input('login_email');
        if ($v === '' || $v === null) {
            $request->merge(['login_email' => null]);
        }
    }

    private function normalizeEmptyOptionals(Request $request): void
    {
        $keys = [
            'nationality_id', 'blood_group_id', 'marital_status_id', 'guardian_relation',
            'guardian_name', 'guardian_phone', 'guardian_email', 'guardian_address',
            'mother_name', 'present_address', 'permanent_address', 'email', 'phone',
            'nid_or_birth_cert_no', 'admission_date', 'date_of_birth',
        ];
        foreach ($keys as $key) {
            if ($request->input($key) === '') {
                $request->merge([$key => null]);
            }
        }
    }
}
