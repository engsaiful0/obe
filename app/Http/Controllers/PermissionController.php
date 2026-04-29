<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $permissions = Permission::with('rules')->get();
            return response()->json(['data' => $permissions]);
        }
        return view('content.apps.app-access-permission');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        $permission = Permission::create(['name' => $request->name]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Permission created successfully.', 'permission' => $permission]);
        }

        return redirect()->route('app-access-permission.index')->with('success', 'Permission created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Permission $permission)
    {
        if ($request->ajax()) {
            return response()->json(['permission' => $permission]);
        }
        return view('content.apps.app-access-permission-edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update(['name' => $request->name]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Permission updated successfully.', 'permission' => $permission]);
        }

        return redirect()->route('app-access-permission.index')->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Permission $permission)
    {
        $permission->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Permission deleted successfully.']);
        }
        return redirect()->route('app-access-permission.index')->with('success', 'Permission deleted successfully.');
    }
}
