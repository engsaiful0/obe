<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\BloodGroup;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeeType;
use App\Models\ExperienceYear;
use App\Models\Gender;
use App\Models\MaritalStatus;
use App\Models\RelatedTo;
use App\Models\Religion;
use App\Models\Rule;
use App\Models\Status;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TeacherController extends Controller
{
    private function teacherFormDropdowns(): array
    {
        $teacherRelatedId = RelatedTo::query()->where('name', 'Teacher')->value('id');

        return [
            'genders' => Gender::query()->orderBy('gender_name')->get(['id', 'gender_name']),
            'teacherStatuses' => Status::query()
                ->where('related_to_id', $teacherRelatedId)
                ->orderBy('status_name')
                ->get(['id', 'status_name']),
            'religions' => Religion::query()->orderBy('religion_name')->get(['id', 'religion_name']),
            'marital_statuses' => MaritalStatus::query()->orderBy('marital_status_name')->get(['id', 'marital_status_name']),
            'blood_groups' => BloodGroup::query()->orderBy('blood_group_name')->get(['id', 'blood_group_name']),
            'employee_types' => EmployeeType::query()->orderBy('employee_type_name')->get(['id', 'employee_type_name']),
            'experience_years' => ExperienceYear::query()->orderBy('experience_year')->get(['id', 'experience_year']),
            'designations' => Designation::query()
                ->where('designation_type', 'Teacher')
                ->orderBy('designation_name')
                ->get(['id', 'designation_name']),
        ];
    }

    /**
     * @return array<int, array{degree: string, subject: string, university: string, passing_year: int, result: ?string}>
     */
    private function sanitizedEducationPayload(array $validatedEducations): array
    {
        return collect($validatedEducations)
            ->filter(fn ($row) => is_array($row) && trim((string) ($row['degree'] ?? '')) !== '')
            ->map(function ($row) {
                $passingYear = $row['passing_year'] ?? null;
                $yearInt = ($passingYear !== null && $passingYear !== '')
                    ? (int) $passingYear
                    : 0;

                return [
                    'degree' => trim((string) $row['degree']),
                    'subject' => isset($row['subject']) ? (string) $row['subject'] : '',
                    'university' => isset($row['university']) ? (string) $row['university'] : '',
                    'passing_year' => $yearInt,
                    'result' => isset($row['result']) && $row['result'] !== '' ? (string) $row['result'] : null,
                ];
            })
            ->values()
            ->all();
    }

    private function syncedTeacherDetailAttributes(array $data): array
    {
        $genderEnum = null;
        if (! empty($data['gender_id'])) {
            $name = Gender::query()->whereKey($data['gender_id'])->value('gender_name');
            $genderEnum = in_array($name, ['Male', 'Female', 'Other'], true) ? $name : 'Other';
        }
        $blood = ! empty($data['blood_group_id'])
            ? BloodGroup::query()->whereKey($data['blood_group_id'])->value('blood_group_name')
            : null;
        $marital = ! empty($data['marital_status_id'])
            ? MaritalStatus::query()->whereKey($data['marital_status_id'])->value('marital_status_name')
            : null;

        return [
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $genderEnum,
            'blood_group' => $blood,
            'nid' => $data['nid'] ?? null,
            'marital_status' => $marital,
            'address' => $data['address'] ?? null,
            'research_area' => $data['research_area'] ?? null,
            'google_scholar_link' => $data['google_scholar_link'] ?? null,
            'orcid_id' => $data['orcid_id'] ?? null,
            'total_publications' => isset($data['total_publications']) ? (int) $data['total_publications'] : 0,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            'emergency_contact_relation' => $data['emergency_contact_relation'] ?? null,
        ];
    }

    public function index(Request $request): View
    {
        $query = Teacher::query()->with([
            'department:id,name',
            'designation:id,designation_name',
            'teacherStatus:id,status_name',
        ]);

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('teacher_name', 'like', "%{$q}%")
                    ->orWhere('employee_id', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', (int) $request->input('department_id'));
        }

        if ($request->filled('status')) {
            $query->whereHas('teacherStatus', function ($sub) use ($request) {
                $sub->where('status_name', $request->input('status'));
            });
        }

        $teachers = $query->latest()->paginate(15)->withQueryString();
        $departments = Department::query()->orderBy('name')->get(['id', 'name']);

        return view('content.teachers.index', compact('teachers', 'departments'));
    }

    public function create(): View
    {
        $departments = Department::query()->orderBy('name')->get(['id', 'name']);

        return view('content.teachers.create', array_merge(compact('departments'), $this->teacherFormDropdowns()));
    }

    public function store(StoreTeacherRequest $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data) {
            $profilePhoto = null;
            if ($request->hasFile('profile_photo')) {
                $profilePhoto = $request->file('profile_photo')->store('teachers/profile-photos', 'public');
            }

            $user = User::create([
                'name' => $data['teacher_name'],
                'email' => $data['login_email'],
                'password' => Hash::make($data['password']),
                'rule_id' => Rule::where('name', 'Teacher')->value('id'),
            ]);
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('Teacher');
            }

            $teacher = Teacher::create([
                'department_id' => $data['department_id'],
                'teacher_name' => $data['teacher_name'],
                'employee_id' => $data['employee_id'],
                'designation_id' => $data['designation_id'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'login_email' => $data['login_email'],
                'password' => Hash::make($data['password']),
                'gender_id' => $data['gender_id'] ?? null,
                'status_id' => $data['status_id'],
                'religion_id' => $data['religion_id'] ?? null,
                'marital_status_id' => $data['marital_status_id'] ?? null,
                'blood_group_id' => $data['blood_group_id'] ?? null,
                'profile_photo' => $profilePhoto,
                'joining_date' => $data['joining_date'] ?? null,
                'employee_type_id' => $data['employee_type_id'],
                'experience_year_id' => $data['experience_year_id'] ?? null,
                'office_room' => $data['office_room'] ?? null,
                'is_program_coordinator' => $request->boolean('is_program_coordinator'),
                'is_course_coordinator' => $request->boolean('is_course_coordinator'),
                'can_submit_clo' => $request->boolean('can_submit_clo'),
                'can_submit_cqi' => $request->boolean('can_submit_cqi'),
                'user_id' => $user->id,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'nid' => $data['nid'] ?? null,
                'address' => $data['address'] ?? null,
                'research_area' => $data['research_area'] ?? null,
                'google_scholar_link' => $data['google_scholar_link'] ?? null,
                'orcid_id' => $data['orcid_id'] ?? null,
                'total_publications' => isset($data['total_publications']) ? (int) $data['total_publications'] : 0,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'emergency_contact_relation' => $data['emergency_contact_relation'] ?? null,
            ]);

            $teacher->detail()->create($this->syncedTeacherDetailAttributes($data));

            $educationRows = $this->sanitizedEducationPayload($data['educations'] ?? []);
            if ($educationRows !== []) {
                $teacher->educations()->createMany($educationRows);
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Teacher created successfully.',
                'redirect_url' => route('teachers.index'),
            ], 201);
        }

        return redirect()->route('teachers.index')->with('success', 'Teacher created successfully.');
    }

    public function show(Teacher $teacher): View
    {
        $teacher->load(['department:id,name', 'designation:id,designation_name', 'teacherStatus:id,status_name', 'detail', 'educations']);

        return view('content.teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher): View
    {
        $teacher->load(['detail', 'educations']);
        $departments = Department::query()->orderBy('name')->get(['id', 'name']);

        return view('content.teachers.edit', array_merge(compact('teacher', 'departments'), $this->teacherFormDropdowns()));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $teacher, $data) {
            $profilePhoto = $teacher->profile_photo;
            if ($request->hasFile('profile_photo')) {
                if ($profilePhoto && Storage::disk('public')->exists($profilePhoto)) {
                    Storage::disk('public')->delete($profilePhoto);
                }
                $profilePhoto = $request->file('profile_photo')->store('teachers/profile-photos', 'public');
            }

            $teacher->update([
                'department_id' => $data['department_id'],
                'teacher_name' => $data['teacher_name'],
                'employee_id' => $data['employee_id'],
                'designation_id' => $data['designation_id'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'login_email' => $data['login_email'],
                'password' => ! empty($data['password']) ? Hash::make($data['password']) : $teacher->password,
                'gender_id' => $data['gender_id'] ?? null,
                'status_id' => $data['status_id'],
                'religion_id' => $data['religion_id'] ?? null,
                'marital_status_id' => $data['marital_status_id'] ?? null,
                'blood_group_id' => $data['blood_group_id'] ?? null,
                'profile_photo' => $profilePhoto,
                'joining_date' => $data['joining_date'] ?? null,
                'employee_type_id' => $data['employee_type_id'],
                'experience_year_id' => $data['experience_year_id'] ?? null,
                'office_room' => $data['office_room'] ?? null,
                'is_program_coordinator' => $request->boolean('is_program_coordinator'),
                'is_course_coordinator' => $request->boolean('is_course_coordinator'),
                'can_submit_clo' => $request->boolean('can_submit_clo'),
                'can_submit_cqi' => $request->boolean('can_submit_cqi'),
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'nid' => $data['nid'] ?? null,
                'address' => $data['address'] ?? null,
                'research_area' => $data['research_area'] ?? null,
                'google_scholar_link' => $data['google_scholar_link'] ?? null,
                'orcid_id' => $data['orcid_id'] ?? null,
                'total_publications' => isset($data['total_publications']) ? (int) $data['total_publications'] : 0,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'emergency_contact_relation' => $data['emergency_contact_relation'] ?? null,
            ]);

            if ($teacher->user_id) {
                $user = User::find($teacher->user_id);
                if ($user) {
                    $userData = [
                        'name' => $data['teacher_name'],
                        'email' => $data['login_email'],
                    ];
                    if (! empty($data['password'])) {
                        $userData['password'] = Hash::make($data['password']);
                    }
                    $user->update($userData);
                    if (method_exists($user, 'assignRole')) {
                        $user->assignRole('Teacher');
                    }
                }
            }

            $teacher->detail()->updateOrCreate(
                ['teacher_id' => $teacher->id],
                $this->syncedTeacherDetailAttributes($data)
            );

            $teacher->educations()->delete();
            $educationRows = $this->sanitizedEducationPayload($data['educations'] ?? []);
            if ($educationRows !== []) {
                $teacher->educations()->createMany($educationRows);
            }
        });

        return redirect()->route('teachers.index')->with('success', 'Teacher updated successfully.');
    }

    public function destroy(Teacher $teacher): \Illuminate\Http\RedirectResponse
    {
        $teacher->delete();

        return redirect()->route('teachers.index')->with('success', 'Teacher deleted successfully.');
    }
}
