<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\Rule;
use App\Models\Teacher as TeacherModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule as ValidationRule;

class Teacher extends Controller
{
    public function index()
    {
        return view('content.settings.teacher');
    }

    public function getTeacher(Request $request)
    {
        $teachers = TeacherModel::with([
            'department:id,name,department_code',
            'designation:id,designation_name',
            'user:id,email',
        ])->orderBy('teacher_name')->get();

        $teachers->each(function ($t) {
            $t->setAttribute('login_email', $t->user?->email);
        });

        return response()->json([
            'data' => $teachers,
        ]);
    }

    public function getTeacherDesignations()
    {
        $designations = Designation::where('designation_type', 'Teacher')
            ->orderBy('designation_name')
            ->get(['id', 'designation_name', 'designation_type']);

        return response()->json(['data' => $designations]);
    }

    public function store(Request $request)
    {
        $this->normalizeOptionalLogin($request);
        $this->baseValidation($request, null);

        if ($request->filled('login_email')) {
            $request->validate([
                'password' => 'required|string|min:6|max:255',
            ]);
        }

        return DB::transaction(function () use ($request) {
            $userId = null;
            if ($request->filled('login_email')) {
                $ruleId = Rule::where('name', 'Teacher')->value('id');
                if (! $ruleId) {
                    abort(422, 'The Teacher role is missing from the rules table. Run database seeders.');
                }
                $userId = User::create([
                    'name' => $request->teacher_name,
                    'email' => $request->login_email,
                    'password' => $request->password,
                    'rule_id' => $ruleId,
                ])->id;
            }

            $teacher = TeacherModel::create([
                'department_id' => $request->department_id,
                'designation_id' => $request->designation_id,
                'teacher_name' => $request->teacher_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'employee_id' => $request->employee_id,
                'user_id' => $userId,
                'status' => $request->status,
            ]);

            $teacher->load([
                'department:id,name,department_code',
                'designation:id,designation_name',
                'user:id,email',
            ]);
            $teacher->setAttribute('login_email', $teacher->user?->email);

            return response()->json($teacher, Response::HTTP_CREATED);
        });
    }

    public function update(Request $request, $id)
    {
        $teacher = TeacherModel::findOrFail($id);
        $this->normalizeOptionalLogin($request);
        $this->baseValidation($request, $teacher->id);

        return DB::transaction(function () use ($request, $teacher) {
            if ($request->filled('login_email')) {
                if ($teacher->user_id) {
                    $user = User::findOrFail($teacher->user_id);
                    $user->update([
                        'name' => $request->teacher_name,
                        'email' => $request->login_email,
                    ]);
                    if ($request->filled('password')) {
                        $user->update(['password' => $request->password]);
                    }
                } else {
                    $request->validate([
                        'password' => 'required|string|min:6|max:255',
                    ]);
                    $ruleId = Rule::where('name', 'Teacher')->value('id');
                    if (! $ruleId) {
                        abort(422, 'The Teacher role is missing from the rules table. Run database seeders.');
                    }
                    $user = User::create([
                        'name' => $request->teacher_name,
                        'email' => $request->login_email,
                        'password' => $request->password,
                        'rule_id' => $ruleId,
                    ]);
                    $teacher->user_id = $user->id;
                }
            } else {
                if ($teacher->user_id) {
                    User::where('id', $teacher->user_id)->delete();
                    $teacher->user_id = null;
                }
            }

            $teacher->fill([
                'department_id' => $request->department_id,
                'designation_id' => $request->designation_id,
                'teacher_name' => $request->teacher_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'employee_id' => $request->employee_id,
                'status' => $request->status,
            ]);
            $teacher->save();

            $teacher->load([
                'department:id,name,department_code',
                'designation:id,designation_name',
                'user:id,email',
            ]);
            $teacher->setAttribute('login_email', $teacher->user?->email);

            return response()->json($teacher);
        });
    }

    public function destroy($id)
    {
        $teacher = TeacherModel::findOrFail($id);

        return DB::transaction(function () use ($teacher) {
            $uid = $teacher->user_id;
            $teacher->delete();
            if ($uid) {
                User::where('id', $uid)->delete();
            }

            return response()->json(null, Response::HTTP_NO_CONTENT);
        });
    }

    private function normalizeOptionalLogin(Request $request): void
    {
        $v = $request->input('login_email');
        if ($v === '' || $v === null) {
            $request->merge(['login_email' => null]);
        }
    }

    private function baseValidation(Request $request, ?int $teacherId): void
    {
        $employeeRule = ValidationRule::unique('teachers', 'employee_id');
        if ($teacherId !== null) {
            $employeeRule = $employeeRule->ignore($teacherId);
        }

        $ignoreUserId = $teacherId !== null
            ? TeacherModel::where('id', $teacherId)->value('user_id')
            : null;

        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'teacher_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'employee_id' => ['required', 'string', 'max:50', $employeeRule],
            'login_email' => [
                'nullable',
                'email',
                'max:255',
                ValidationRule::unique('users', 'email')->ignore($ignoreUserId),
            ],
            'password' => 'nullable|string|min:6|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);
    }
}
