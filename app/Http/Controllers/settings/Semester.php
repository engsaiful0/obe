<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Semester as SemesterModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Semester extends Controller
{
    public function index()
    {
        $programs = Program::with(['faculty:id,faculty_name', 'department:id,name'])
            ->orderBy('program_name')
            ->get(['id', 'program_name', 'program_code', 'faculty_id', 'department_id']);

        return view('content.settings.semester', compact('programs'));
    }

    public function getSemester(Request $request)
    {
        $rows = SemesterModel::with(['program:id,program_name,program_code'])
            ->orderBy('program_id')
            ->orderBy('semester_order')
            ->get();

        return response()->json([
            'data' => $rows,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedSemester($request);

        $user = Auth::user();

        $semester = SemesterModel::create(array_merge($data, [
            'user_id' => $user->id,
        ]));

        $semester->load('program:id,program_name,program_code');

        return response()->json($semester, Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $data = $this->validatedSemester($request, $id);

        $semester = SemesterModel::findOrFail($id);
        $semester->update($data);

        $semester->load('program:id,program_name,program_code');

        return response()->json($semester);
    }

    public function destroy($id)
    {
        $semester = SemesterModel::findOrFail($id);
        $semester->delete();

        return response()->json(['success' => true]);
    }

    /**
     * @param  int|string|null  $ignoreId
     */
    protected function validatedSemester(Request $request, $ignoreId = null): array
    {
        $nameRule = Rule::unique('semesters', 'semester_name')
            ->where(fn ($q) => $q->where('program_id', (int) $request->input('program_id')));

        $orderRule = Rule::unique('semesters', 'semester_order')
            ->where(fn ($q) => $q->where('program_id', (int) $request->input('program_id')));

        if ($ignoreId !== null) {
            $nameRule = $nameRule->ignore($ignoreId);
            $orderRule = $orderRule->ignore($ignoreId);
        }

        return $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'semester_name' => ['required', 'string', 'max:255', $nameRule],
            'semester_order' => ['required', 'integer', 'min:1', 'max:32767', $orderRule],
            'status' => ['required', 'in:Active,Inactive'],
        ]);
    }
}
