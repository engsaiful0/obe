<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Program as ProgramModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Program extends Controller
{
    public function index()
    {
        $faculties = Faculty::orderBy('faculty_name')->get(['id', 'faculty_name', 'faculty_code']);

        $departments = Department::orderBy('name')
            ->get(['id', 'name', 'faculty_id']);

        return view('content.settings.program', compact('faculties', 'departments'));
    }

    public function getProgram(Request $request)
    {
        $programs = ProgramModel::with([
            'faculty:id,faculty_name,faculty_code',
            'department:id,name,department_code',
        ])
            ->orderBy('program_name')
            ->get();

        return response()->json([
            'data' => $programs,
        ]);
    }

    public function store(Request $request)
    {
        $this->validateProgram($request);

        $user = Auth::user();

        $program = ProgramModel::create([
            'faculty_id' => $request->faculty_id,
            'department_id' => $request->department_id,
            'program_name' => $request->program_name,
            'program_code' => $request->program_code,
            'degree_level' => $request->degree_level,
            'duration' => $request->duration,
            'total_semester' => $request->total_semester,
            'total_credit' => $request->total_credit,
            'status' => $request->status,
            'user_id' => $user->id,
        ]);

        $program->load(['faculty:id,faculty_name', 'department:id,name,department_code']);

        return response()->json($program, Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $this->validateProgram($request, $id);

        $program = ProgramModel::findOrFail($id);
        $program->update([
            'faculty_id' => $request->faculty_id,
            'department_id' => $request->department_id,
            'program_name' => $request->program_name,
            'program_code' => $request->program_code,
            'degree_level' => $request->degree_level,
            'duration' => $request->duration,
            'total_semester' => $request->total_semester,
            'total_credit' => $request->total_credit,
            'status' => $request->status,
        ]);

        $program->load(['faculty:id,faculty_name', 'department:id,name,department_code']);

        return response()->json($program);
    }

    public function destroy($id)
    {
        $program = ProgramModel::findOrFail($id);
        $program->delete();

        return response()->json(['success' => true]);
    }

    protected function normalizeProgramInput(Request $request): void
    {
        foreach (['total_semester', 'total_credit'] as $field) {
            $v = $request->input($field);
            if ($v === null || $v === '') {
                $request->merge([$field => null]);
            } elseif (is_numeric($v)) {
                $request->merge([$field => (int) $v]);
            }
        }

        $duration = $request->input('duration');
        if ($duration === '') {
            $request->merge(['duration' => null]);
        }
    }

    /**
     * @param  int|null  $ignoreId  Program id when updating.
     */
    protected function validateProgram(Request $request, $ignoreId = null): void
    {
        $this->normalizeProgramInput($request);

        $codeRule = 'required|string|max:50|unique:programs,program_code';
        if ($ignoreId !== null) {
            $codeRule .= ','.$ignoreId;
        }

        $request->validate([
            'faculty_id' => ['required', 'exists:faculties,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'program_name' => ['required', 'string', 'max:255'],
            'program_code' => $codeRule,
            'degree_level' => ['required', 'in:Bachelor,Masters,PhD'],
            'duration' => ['nullable', 'string', 'max:255'],
            'total_semester' => ['nullable', 'integer', 'min:0', 'max:32767'],
            'total_credit' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        $dept = Department::find($request->department_id);
        if (! $dept || (int) $dept->faculty_id !== (int) $request->faculty_id) {
            throw ValidationException::withMessages([
                'department_id' => ['The selected department must belong to the selected faculty.'],
            ]);
        }
    }
}
