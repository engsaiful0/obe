<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Batch as BatchModel;
use App\Models\Program;
use App\Models\RelatedTo;
use App\Models\Status as StatusModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class Batch extends Controller
{
    public function index()
    {
        $programs = Program::orderBy('program_name')
            ->get(['id', 'program_name', 'program_code']);

        $academicSessions = AcademicSession::orderByDesc('academic_year')
            ->orderBy('session_name')
            ->get(['id', 'session_name', 'academic_year']);

        $batchStatuses = $this->batchStatusOptionsForCurrentUser();

        return view('content.settings.batch', compact('programs', 'academicSessions', 'batchStatuses'));
    }

    /**
     * Status rows for Related To "Batch", scoped to current user.
     */
    protected function batchStatusOptionsForCurrentUser()
    {
        $batchRelatedToId = RelatedTo::query()->where('name', 'Batch')->value('id');
        if (! $batchRelatedToId) {
            return collect();
        }

        return StatusModel::query()
            ->where('user_id', Auth::id())
            ->where('related_to_id', $batchRelatedToId)
            ->orderBy('status_name')
            ->get(['id', 'status_name']);
    }

    protected function legacyBatchStatusEnumFromStatusName(string $statusName): string
    {
        $n = Str::lower(trim($statusName));
        if ($n === 'completed' || Str::contains($n, 'completed')) {
            return 'Completed';
        }
        if ($n === 'inactive' || Str::contains($n, 'inactive')) {
            return 'Inactive';
        }
        if ($n === 'running' || Str::contains($n, 'running')) {
            return 'Running';
        }

        return 'Running';
    }

    public function getBatch(Request $request)
    {
        $rows = BatchModel::with([
            'program:id,program_name,program_code',
            'academicSession:id,session_name,academic_year',
            'batchStatus:id,status_name',
        ])
            ->orderByDesc('start_date')
            ->get();

        return response()->json([
            'data' => $rows,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedBatch($request);

        $user = Auth::user();

        $batch = BatchModel::create(array_merge($data, [
            'user_id' => $user->id,
        ]));

        $batch->load([
            'program:id,program_name',
            'academicSession:id,session_name,academic_year',
            'batchStatus:id,status_name',
        ]);

        return response()->json($batch, Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $data = $this->validatedBatch($request, $id);

        $batch = BatchModel::findOrFail($id);
        $batch->update($data);

        return response()->json(
            $batch->fresh()->load([
                'program:id,program_name,program_code',
                'academicSession:id,session_name,academic_year',
                'batchStatus:id,status_name',
            ])
        );
    }

    public function destroy($id)
    {
        $batch = BatchModel::findOrFail($id);
        $batch->delete();

        return response()->json(['success' => true]);
    }

    /**
     * @param  int|string|null  $ignoreId
     */
    protected function validatedBatch(Request $request, $ignoreId = null): array
    {
        $batchNameRule = Rule::unique('batches', 'batch_name')
            ->where(fn ($q) => $q->where('program_id', (int) $request->input('program_id')));

        $batchCodeRule = Rule::unique('batches', 'batch_code');

        if ($ignoreId !== null) {
            $batchNameRule = $batchNameRule->ignore($ignoreId);
            $batchCodeRule = $batchCodeRule->ignore($ignoreId);
        }

        $batchRelatedToId = RelatedTo::query()->where('name', 'Batch')->value('id');

        $statusIdRule = Rule::exists('statuses', 'id')->where(
            fn ($q) => $q->where('user_id', Auth::id())
                ->when($batchRelatedToId, fn ($qq) => $qq->where('related_to_id', $batchRelatedToId))
        );

        $data = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'batch_name' => ['required', 'string', 'max:255', $batchNameRule],
            'batch_code' => ['required', 'string', 'max:50', $batchCodeRule],
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'start_date' => ['required', 'date'],
            'expected_passing_year' => ['required', 'integer', 'digits:4', 'min:1990', 'max:2100'],
            'status_id' => ['required', 'integer', $statusIdRule],
        ]);

        $statusRow = StatusModel::query()->findOrFail($data['status_id']);
        $data['status'] = $this->legacyBatchStatusEnumFromStatusName($statusRow->status_name);

        return $data;
    }
}
