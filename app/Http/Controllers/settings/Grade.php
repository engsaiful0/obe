<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\Grade as GradeModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Grade extends Controller
{
    public function index()
    {
        return view('content.settings.grade');
    }

    public function getGrades(Request $request)
    {
        $grades = GradeModel::query()
            ->orderBy('from_marks')
            ->orderBy('to_marks')
            ->get();

        return response()->json(['data' => $grades]);
    }

    public function store(Request $request)
    {
        $input = $this->normalizedGradeInput($request);
        $validator = Validator::make($input, $this->rules());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $validator->validated();

        $grade = GradeModel::create([
            'from_marks' => round((float) $data['from_marks'], 2),
            'to_marks' => round((float) $data['to_marks'], 2),
            'grade_name' => trim((string) $data['grade_name']),
            'grade_point' => round((float) $data['grade_point'], 2),
        ]);

        return response()->json([
            'message' => __('Grade created successfully.'),
            'data' => $grade,
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, int $id)
    {
        $grade = GradeModel::query()->findOrFail($id);

        $input = $this->normalizedGradeInput($request);
        $validator = Validator::make($input, $this->rules($id));

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $validator->validated();

        $grade->update([
            'from_marks' => round((float) $data['from_marks'], 2),
            'to_marks' => round((float) $data['to_marks'], 2),
            'grade_name' => trim((string) $data['grade_name']),
            'grade_point' => round((float) $data['grade_point'], 2),
        ]);

        return response()->json([
            'message' => __('Grade updated successfully.'),
            'data' => $grade->fresh(),
        ]);
    }

    public function destroy(int $id)
    {
        $grade = GradeModel::query()->findOrFail($id);
        $grade->delete();

        return response()->json(['message' => __('Grade deleted successfully.')]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(?int $ignoreId = null): array
    {
        $gradeNameUnique = Rule::unique('grades', 'grade_name');
        if ($ignoreId !== null) {
            $gradeNameUnique = $gradeNameUnique->ignore($ignoreId);
        }

        return [
            'from_marks' => ['required', 'numeric', 'min:0'],
            'to_marks' => ['required', 'numeric', 'gte:from_marks'],
            'grade_name' => ['required', 'string', 'max:100', $gradeNameUnique],
            'grade_point' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalizedGradeInput(Request $request): array
    {
        $input = $request->only(['from_marks', 'to_marks', 'grade_name', 'grade_point']);

        foreach (['from_marks', 'to_marks'] as $key) {
            if (isset($input[$key]) && is_string($input[$key])) {
                $input[$key] = str_replace(',', '.', trim($input[$key]));
            }
        }

        $gp = $input['grade_point'] ?? null;
        if ($gp === null || $gp === '' || (is_string($gp) && trim($gp) === '')) {
            $input['grade_point'] = '0';
        } elseif (is_string($gp)) {
            $input['grade_point'] = str_replace(',', '.', trim($gp));
        }

        return $input;
    }
}
