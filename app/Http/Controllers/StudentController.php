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
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule as ValidationRule;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    public const GUARDIAN_RELATIONS = [
        'Father', 'Mother', 'Legal Guardian', 'Brother', 'Sister', 'Uncle', 'Aunt',
        'Grandfather', 'Grandmother', 'Other',
    ];

    public function create()
    {
        return view('content.student.create', [
            'guardian_relations' => self::GUARDIAN_RELATIONS,
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

        $batches = Batch::query()
            ->where('program_id', $request->program_id)
            ->where('status', 'Active')
            ->with('academicSession:id,session_name')
            ->orderBy('batch_name')
            ->get(['id', 'program_id', 'batch_name', 'batch_code', 'academic_session_id']);

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
            'status' => 'required|in:Active,Inactive',
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
                'status' => $validated['status'],
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
