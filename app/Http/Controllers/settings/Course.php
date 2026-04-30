<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\Course as CourseModel;
use App\Models\Program;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class Course extends Controller
{
    public function index()
    {
        $programs = Program::orderBy('program_name')
            ->get(['id', 'program_name', 'program_code']);

        $semesters = Semester::orderBy('program_id')
            ->orderBy('semester_order')
            ->get(['id', 'program_id', 'semester_name', 'semester_order']);

        return view('content.settings.course', compact('programs', 'semesters'));
    }

    public function getCourse(Request $request)
    {
        $rows = CourseModel::with([
            'program:id,program_name,program_code',
            'semester:id,semester_name,semester_order',
        ])
            ->orderBy('program_id')
            ->orderBy('semester_id')
            ->orderBy('course_code')
            ->get();

        return response()->json([
            'data' => $rows,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedCourse($request);

        $user = Auth::user();

        $course = CourseModel::create(array_merge($data, [
            'user_id' => $user->id,
        ]));

        $course->load(['program:id,program_name', 'semester:id,semester_name']);

        return response()->json($course, Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $data = $this->validatedCourse($request, $id);

        $course = CourseModel::findOrFail($id);
        $course->update($data);

        $course->load(['program:id,program_name', 'semester:id,semester_name']);

        return response()->json($course->fresh());
    }

    public function destroy($id)
    {
        $course = CourseModel::findOrFail($id);
        $course->delete();

        return response()->json(['success' => true]);
    }

    /**
     * @param  int|string|null  $ignoreId
     */
    protected function validatedCourse(Request $request, $ignoreId = null): array
    {
        $codeRule = Rule::unique('courses', 'course_code')
            ->where(fn ($q) => $q->where('program_id', (int) $request->input('program_id')));

        if ($ignoreId !== null) {
            $codeRule = $codeRule->ignore($ignoreId);
        }

        $validated = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'course_code' => ['required', 'string', 'max:50', $codeRule],
            'course_title' => ['required', 'string', 'max:255'],
            'credit' => ['required', 'numeric', 'min:0', 'max:999.99'],
            'course_type' => ['required', 'in:Theory,Lab,Project,Thesis,Viva,Internship'],
            'contact_hour' => ['required', 'integer', 'min:0', 'max:32767'],
            'marks' => ['required', 'integer', 'min:0', 'max:32767'],
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        $semester = Semester::find($request->input('semester_id'));
        if (! $semester || (int) $semester->program_id !== (int) $request->input('program_id')) {
            throw ValidationException::withMessages([
                'semester_id' => ['The selected semester must belong to the selected program.'],
            ]);
        }

        return $validated;
    }
}
