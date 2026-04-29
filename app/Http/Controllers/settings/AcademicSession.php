<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession as AcademicSessionModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AcademicSession extends Controller
{
    public function index()
    {
        return view('content.settings.academic-session');
    }

    public function getAcademicSession(Request $request)
    {
        $rows = AcademicSessionModel::orderByDesc('academic_year')
            ->orderBy('session_name')
            ->get();

        return response()->json([
            'data' => $rows,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $user = Auth::user();

        $row = AcademicSessionModel::create(array_merge($data, [
            'user_id' => $user->id,
        ]));

        return response()->json($row, Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $data = $this->validatedData($request, $id);

        $row = AcademicSessionModel::findOrFail($id);
        $row->update($data);

        return response()->json($row->fresh());
    }

    public function destroy($id)
    {
        $row = AcademicSessionModel::findOrFail($id);
        $row->delete();

        return response()->json(['success' => true]);
    }

    /**
     * @param  int|string|null  $ignoreId  Academic session id when updating.
     */
    protected function validatedData(Request $request, $ignoreId = null): array
    {
        $sessionNameUnique = Rule::unique('academic_sessions', 'session_name')
            ->where(fn ($query) => $query->where('academic_year', (int) $request->input('academic_year')));

        if ($ignoreId !== null) {
            $sessionNameUnique = $sessionNameUnique->ignore($ignoreId);
        }

        return $request->validate([
            'session_name' => [
                'required',
                'string',
                'max:255',
                $sessionNameUnique,
            ],
            'academic_year' => ['required', 'integer', 'digits:4', 'min:1990', 'max:2100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:Active,Inactive'],
        ]);
    }
}
