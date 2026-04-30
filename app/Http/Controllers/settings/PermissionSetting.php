<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\Permission as PermissionModel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PermissionSetting extends Controller
{
    public function index()
    {
        return view('content.settings.permission');
    }

    public function getPermission(Request $request)
    {
        $permissions = PermissionModel::with(['rules:id,name'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        $permission = PermissionModel::create([
            'name' => $request->name,
            'user_id' => Auth::id(),
        ]);

        $permission->load(['rules:id,name']);

        return response()->json($permission, Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,'.$id,
        ]);

        $permission = PermissionModel::findOrFail($id);
        $permission->update([
            'name' => $request->name,
        ]);

        $permission->load(['rules:id,name']);

        return response()->json($permission);
    }

    public function destroy($id)
    {
        $permission = PermissionModel::findOrFail($id);
        $permission->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
