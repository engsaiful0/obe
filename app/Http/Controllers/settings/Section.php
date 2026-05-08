<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Section as SectionModel;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class Section extends Controller
{
    public function index()
    {
        return view('content.settings.section');
    }

    public function getSections(Request $request)
    {
        $sections = SectionModel::with([
            'faculty:id,faculty_name,faculty_code',
            'department:id,name,department_code',
            'program:id,program_name,program_code',
            'semester:id,semester_name,semester_order',
        ])
            ->orderBy('section_name')
            ->get();

        return response()->json(['data' => $sections]);
    }

    public function departmentsByFaculty(Request $request)
    {
        $request->validate(['faculty_id' => 'required|exists:faculties,id']);

        $items = Department::query()
            ->where('faculty_id', $request->faculty_id)
            ->where('status', 'Active')
            ->orderBy('name')
            ->get(['id', 'name', 'department_code', 'faculty_id']);

        return response()->json(['data' => $items]);
    }

    public function programsByDepartment(Request $request)
    {
        $request->validate(['department_id' => 'required|exists:departments,id']);

        $items = Program::query()
            ->where('department_id', $request->department_id)
            ->where('status', 'Active')
            ->orderBy('program_name')
            ->get(['id', 'program_name', 'program_code', 'department_id', 'faculty_id']);

        return response()->json(['data' => $items]);
    }

    public function batchesByProgram(Request $request)
    {
        $request->validate(['program_id' => 'required|exists:programs,id']);

        $items = Batch::query()
            ->where('program_id', $request->program_id)
            ->orderBy('batch_name')
            ->get(['id', 'batch_name', 'batch_code', 'program_id']);

        return response()->json(['data' => $items]);
    }

    public function semestersByProgram(Request $request)
    {
        $request->validate(['program_id' => 'required|exists:programs,id']);

        $items = Semester::query()
            ->where('program_id', $request->program_id)
            ->where('status', 'Active')
            ->orderBy('semester_order')
            ->orderBy('semester_name')
            ->get(['id', 'semester_name', 'semester_order', 'program_id']);

        return response()->json(['data' => $items]);
    }

    public function store(Request $request)
    {
        $this->validateSection($request);

        return $this->saveSection(new SectionModel, $request, Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $section = SectionModel::findOrFail($id);
        $this->validateSection($request, $section->id);

        return $this->saveSection($section, $request);
    }

    public function destroy($id)
    {
        $section = SectionModel::findOrFail($id);
        $section->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    private function validateSection(Request $request, ?int $sectionId = null): void
    {
        $program = Program::where('id', $request->program_id)->first();

        $rules = [
            'faculty_id' => 'required|exists:faculties,id',
            'department_id' => 'required|exists:departments,id',
            'program_id' => 'required|exists:programs,id',
            'semester_id' => 'required|exists:semesters,id',
            'section_name' => 'required|string|max:255',
            'section_code' => [
                'required',
                'string',
                'max:80',
                Rule::unique('sections', 'section_code')
                    ->where(fn($q) => $q
                        ->where('program_id', (int) $request->program_id)
                        ->where('semester_id', (int) $request->semester_id))
                    ->ignore($sectionId),
            ],
            'gender_type' => 'required|in:Male,Female,Combined',
            'capacity' => 'required|integer|min:0|max:100000',
            'class_room' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ];

        $request->validate($rules);

        $this->assertHierarchy($request, $program);
    }

    private function assertHierarchy(Request $request, ?Program $program): void
    {
        if (!$program ||
                $program->department_id !== (int) $request->department_id ||
                $program->faculty_id !== (int) $request->faculty_id) {
            throw ValidationException::withMessages([
                'program_id' => 'The program does not match the selected faculty and department.',
            ]);
        }

       

        $semesterBelongs = Semester::where('id', $request->semester_id)
            ->where('program_id', $request->program_id)
            ->exists();
        if (!$semesterBelongs) {
            throw ValidationException::withMessages([
                'semester_id' => 'The semester does not belong to the selected program.',
            ]);
        }

        $deptFacultyOk = Department::where('id', $request->department_id)
            ->where('faculty_id', $request->faculty_id)
            ->exists();
        if (!$deptFacultyOk) {
            throw ValidationException::withMessages([
                'department_id' => 'The department does not belong to the selected faculty.',
            ]);
        }
    }

    private function saveSection(SectionModel $section, Request $request, ?int $httpCreated = null)
    {
        $userId = Auth::id();

        $section->fill([
            'faculty_id' => $request->faculty_id,
            'department_id' => $request->department_id,
            'program_id' => $request->program_id,
            'semester_id' => $request->semester_id,
            'section_name' => $request->section_name,
            'section_code' => $request->section_code,
            'gender_type' => $request->gender_type,
            'capacity' => $request->capacity,
            'class_room' => $request->class_room,
            'status' => $request->status,
        ]);

        if (!$section->exists && $userId) {
            $section->user_id = $userId;
        }

        $section->save();

        $section->load([
            'faculty:id,faculty_name,faculty_code',
            'department:id,name,department_code',
            'program:id,program_name,program_code',
            'semester:id,semester_name,semester_order',
        ]);

        return response()->json($section, $httpCreated ?? 200);
    }
}
