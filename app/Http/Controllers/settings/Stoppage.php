<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Stoppage as StoppageModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Stoppage extends Controller
{
    public function index()
    {
        return view('content.settings.stoppage');
    }

    public function getStoppage(Request $request)
    {
        $stoppages = StoppageModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $stoppages,
        ]);
    }

    public function show($id)
    {
        try {
            $stoppage = StoppageModel::where('id', $id)->where('user_id', Auth::id())->first();
            
            if (!$stoppage) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'error' => 'Stoppage not found',
                    ], 404);
                }
                abort(404, 'Stoppage not found');
            }
            
            // Ensure status has a default value if column doesn't exist yet
            if (!isset($stoppage->status)) {
                $stoppage->status = StoppageModel::STATUS_ACTIVE;
            }
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'data' => $stoppage,
                ]);
            }
            
            return redirect()->route('app-settings-stoppage');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database errors (e.g., missing column)
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'error' => 'Database error. Please run migrations: ' . $e->getMessage(),
                ], 500);
            }
            abort(500, 'Database error. Please run migrations.');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'error' => 'An error occurred: ' . $e->getMessage(),
                ], 500);
            }
            abort(500, 'An error occurred');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'stoppage_name' => 'required|string|max:255|unique:stoppages,stoppage_name,NULL,id,user_id,' . Auth::id(),
            'status' => 'required|in:active,inactive',
            'distance' => 'nullable|numeric|min:0|max:999999.99',
        ]);

        $user = Auth::user();
        $userId = $user->id;
        
        $stoppage = StoppageModel::create([
            'stoppage_name' => $request->stoppage_name,
            'status' => $request->status ?? StoppageModel::STATUS_ACTIVE,
            'distance' => $request->distance ?? null,
            'user_id' => $userId,
        ]);
        
        return response()->json(['message' => 'Stoppage created successfully.', 'data' => $stoppage], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'stoppage_name' => 'required|string|max:255|unique:stoppages,stoppage_name,' . $id . ',id,user_id,' . Auth::id(),
            'status' => 'required|in:active,inactive',
            'distance' => 'nullable|numeric|min:0|max:999999.99',
        ]);

        $stoppage = StoppageModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $stoppage->update([
            'stoppage_name' => $request->stoppage_name,
            'status' => $request->status,
            'distance' => $request->distance ?? null,
        ]);

        return response()->json(['message' => 'Stoppage updated successfully.', 'data' => $stoppage]);
    }

    public function destroy($id)
    {
        try {
            $stoppage = StoppageModel::where('id', $id)->where('user_id', Auth::id())->first();
            
            if (!$stoppage) {
                return response()->json([
                    'error' => 'Stoppage not found or you do not have permission to delete it.'
                ], 404);
            }
            
            $stoppage->delete();

            return response()->json(['message' => 'Stoppage deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while deleting the stoppage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get distance for a specific stoppage
     */
    public function getDistance(Request $request)
    {
        $request->validate([
            'stoppage_id' => 'required|exists:stoppages,id'
        ]);

        $stoppage = StoppageModel::where('id', $request->stoppage_id)
            ->first();

        if (!$stoppage) {
            return response()->json([
                'success' => false,
                'message' => 'Stoppage not found.',
                'distance' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'distance' => $stoppage->distance ?? null
        ]);
    }
}
