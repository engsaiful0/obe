<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusRoute as BusRouteModel;
use App\Models\Stoppage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class BusRoute extends Controller
{
    public function index()
    {
        return view('content.settings.bus-route');
    }

    public function getBusRoutes(Request $request)
    {
        $busRoutes = BusRouteModel::with(['startStoppage', 'endStoppage'])
            ->where('user_id', Auth::id())
            ->get();
        return response()->json([
            'data' => $busRoutes,
        ]);
    }

    public function getStoppages(Request $request)
    {
        $stoppages = Stoppage::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $stoppages,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'route_name' => 'required|string|max:255|unique:bus_routes,route_name,NULL,id,user_id,' . Auth::id(),
            'description' => 'nullable|string',
            'start_stoppage_id' => 'required|exists:stoppages,id',
            'end_stoppage_id' => 'required|exists:stoppages,id|different:start_stoppage_id',
            'distance' => 'nullable|numeric|min:0',
            'estimated_time' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Additional validation to ensure stoppages belong to the user
        $startStoppage = Stoppage::where('id', $request->start_stoppage_id)
            ->where('user_id', Auth::id())
            ->first();
        $endStoppage = Stoppage::where('id', $request->end_stoppage_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$startStoppage || !$endStoppage) {
            return response()->json(['error' => 'Invalid stoppage selection.'], 422);
        }

        $user = Auth::user();
        $userId = $user->id;
        
        $busRoute = BusRouteModel::create([
            'route_name' => $request->route_name,
            'description' => $request->description,
            'start_stoppage_id' => $request->start_stoppage_id,
            'end_stoppage_id' => $request->end_stoppage_id,
            'distance' => $request->distance,
            'estimated_time' => $request->estimated_time,
            'is_active' => $request->is_active ?? true,
            'user_id' => $userId,
        ]);
        
        return response()->json(['message' => 'Bus route created successfully.', 'data' => $busRoute], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'route_name' => 'required|string|max:255|unique:bus_routes,route_name,' . $id . ',id,user_id,' . Auth::id(),
            'description' => 'nullable|string',
            'start_stoppage_id' => 'required|exists:stoppages,id',
            'end_stoppage_id' => 'required|exists:stoppages,id|different:start_stoppage_id',
            'distance' => 'nullable|numeric|min:0',
            'estimated_time' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Additional validation to ensure stoppages belong to the user
        $startStoppage = Stoppage::where('id', $request->start_stoppage_id)
            ->where('user_id', Auth::id())
            ->first();
        $endStoppage = Stoppage::where('id', $request->end_stoppage_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$startStoppage || !$endStoppage) {
            return response()->json(['error' => 'Invalid stoppage selection.'], 422);
        }

        $busRoute = BusRouteModel::where('id', $id)->firstOrFail();
        $busRoute->update([
            'route_name' => $request->route_name,
            'description' => $request->description,
            'start_stoppage_id' => $request->start_stoppage_id,
            'end_stoppage_id' => $request->end_stoppage_id,
            'distance' => $request->distance,
            'estimated_time' => $request->estimated_time,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json(['message' => 'Bus route updated successfully.', 'data' => $busRoute]);
    }

    public function destroy($id)
    {
        $busRoute = BusRouteModel::where('id', $id)->firstOrFail();
        $busRoute->delete();

        return response()->json(['message' => 'Bus route deleted successfully.']);
    }
}
