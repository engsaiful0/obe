<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\Department;
use App\Models\Rule;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Gender;
use App\Models\Status;
use App\Models\Religion;
use App\Models\MaritalStatus;
use App\Models\BloodGroup;
use App\Models\EmployeeType;
use App\Models\RelatedTo;
use App\Models\ExperienceYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $query = Teacher::query()->with(['department:id,name']);

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('employee_id', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', (int) $request->input('department_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $teachers = $query->latest()->paginate(15)->withQueryString();
        $departments = Department::query()->orderBy('name')->get(['id', 'name']);

        return view('content.teachers.index', compact('teachers', 'departments'));
    }

    public function create(): View
    {
        $genders = Gender::query()->orderBy('gender_name')->get(['id', 'gender_name']);
        

        $teacherStatuses = Status::query()
        ->where('related_to_id', RelatedTo::where('name', 'Teacher')->value('id'))
        ->orderBy('status_name')
        ->get(['id', 'status_name']);

        $religions = Religion::query()->orderBy('religion_name')->get(['id', 'religion_name']);
        $marital_statuses = MaritalStatus::query()->orderBy('marital_status_name')->get(['id', 'marital_status_name']);
        $blood_groups = BloodGroup::query()->orderBy('blood_group_name')->get(['id', 'blood_group_name']);

        $employee_types = EmployeeType::query()->orderBy('employee_type_name')->get(['id', 'employee_type_name']);

        $experience_years = ExperienceYear::query()->orderBy('experience_year')->get(['id', 'experience_year']);
        $departments = Department::query()->orderBy('name')->get(['id', 'name']);
        return view('content.teachers.create', compact('departments', 'genders', 'teacherStatuses', 'religions', 'marital_statuses', 'blood_groups', 'employee_types', 'experience_years'));
    }

    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data) {
            $profilePhoto = null;
            if ($request->hasFile('profile_photo')) {
                $profilePhoto = $request->file('profile_photo')->store('teachers/profile-photos', 'public');
            }

            $user = User::create([
                'name' => $data['name'],
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
                'gender_id' => $data['gender_id'],
                'status_id' => $data['status_id'],
                'religion_id' => $data['religion_id'],
                'marital_status_id' => $data['marital_status_id'],
                'blood_group_id' => $data['blood_group_id'],
                'profile_photo' => $profilePhoto,
                'joining_date' => $data['joining_date'] ?? null,
                'employment_type_id' => $data['employment_type_id'],
                'experience_years' => $data['experience_years'] ?? 0,
                'office_room' => $data['office_room'] ?? null,
                'is_program_coordinator' => $request->boolean('is_program_coordinator'),
                'is_course_coordinator' => $request->boolean('is_course_coordinator'),
                'can_submit_clo' => $request->boolean('can_submit_clo'),
                'can_submit_cqi' => $request->boolean('can_submit_cqi'),
                'user_id' => $user->id,
            ]);

            $teacher->detail()->create([
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'nid' => $data['nid'] ?? null,
                'address' => $data['address'] ?? null,
                'research_area' => $data['research_area'] ?? null,
                'google_scholar_link' => $data['google_scholar_link'] ?? null,
                'orcid_id' => $data['orcid_id'] ?? null,
                'total_publications' => $data['total_publications'] ?? 0,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'emergency_contact_relation' => $data['emergency_contact_relation'] ?? null,
            ]);

            $educations = collect($data['educations'] ?? [])->filter(function ($row) {
                return ! empty($row['degree']) || ! empty($row['subject']) || ! empty($row['university']);
            })->values();
            if ($educations->isNotEmpty()) {
                $teacher->educations()->createMany($educations->all());
            }
        });

        return redirect()->route('teachers.index')->with('success', 'Teacher created successfully.');
    }

    public function show(Teacher $teacher): View
    {
        $teacher->load(['department:id,name', 'detail', 'educations']);
        return view('content.teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher): View
    {
        $teacher->load(['detail', 'educations']);
        $departments = Department::query()->orderBy('name')->get(['id', 'name']);
        return view('content.teachers.edit', compact('teacher', 'departments'));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher): RedirectResponse
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
                'name' => $data['name'],
                'teacher_name' => $data['name'],
                'employee_id' => $data['employee_id'],
                'designation' => $data['designation'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'login_email' => $data['login_email'],
                'password' => ! empty($data['password']) ? Hash::make($data['password']) : $teacher->password,
                'status' => $data['status'],
                'profile_photo' => $profilePhoto,
                'joining_date' => $data['joining_date'] ?? null,
                'employment_type' => $data['employment_type'],
                'experience_years' => $data['experience_years'] ?? 0,
                'office_room' => $data['office_room'] ?? null,
                'is_program_coordinator' => $request->boolean('is_program_coordinator'),
                'is_course_coordinator' => $request->boolean('is_course_coordinator'),
                'can_submit_clo' => $request->boolean('can_submit_clo'),
                'can_submit_cqi' => $request->boolean('can_submit_cqi'),
            ]);

            if ($teacher->user_id) {
                $user = User::find($teacher->user_id);
                if ($user) {
                    $userData = [
                        'name' => $data['name'],
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
                [
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'blood_group' => $data['blood_group'] ?? null,
                    'nid' => $data['nid'] ?? null,
                    'marital_status' => $data['marital_status'] ?? null,
                    'address' => $data['address'] ?? null,
                    'research_area' => $data['research_area'] ?? null,
                    'google_scholar_link' => $data['google_scholar_link'] ?? null,
                    'orcid_id' => $data['orcid_id'] ?? null,
                    'total_publications' => $data['total_publications'] ?? 0,
                    'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                    'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                    'emergency_contact_relation' => $data['emergency_contact_relation'] ?? null,
                ]
            );

            $teacher->educations()->delete();
            $educations = collect($data['educations'] ?? [])->filter(function ($row) {
                return ! empty($row['degree']) || ! empty($row['subject']) || ! empty($row['university']);
            })->values();
            if ($educations->isNotEmpty()) {
                $teacher->educations()->createMany($educations->all());
            }
        });

        return redirect()->route('teachers.index')->with('success', 'Teacher updated successfully.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        $teacher->delete();
        return redirect()->route('teachers.index')->with('success', 'Teacher deleted successfully.');
    }
}
