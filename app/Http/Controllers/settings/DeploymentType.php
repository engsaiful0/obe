<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeploymentType as DeploymentTypeModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class DeploymentType extends Controller
{
    public function index()
    {
        return view('content.settings.deployment-type');
    }

    public function getDeploymentType(Request $request)
    {
        $deploymentTypes = DeploymentTypeModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $deploymentTypes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'deployment_type_name' => 'required|string|max:255|unique:deployment_types,deployment_type_name,NULL,id,user_id,' . Auth::id(),
        ]);

        $user = Auth::user();
        $userId = $user->id;
        
        $deploymentType = DeploymentTypeModel::create([
            'deployment_type_name' => $request->deployment_type_name,
            'user_id' => $userId,
        ]);
        
        return response()->json(['message' => 'Deployment Type created successfully.', 'data' => $deploymentType], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'deployment_type_name' => 'required|string|max:255|unique:deployment_types,deployment_type_name,' . $id . ',id,user_id,' . Auth::id(),
        ]);

        $deploymentType = DeploymentTypeModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $deploymentType->update([
            'deployment_type_name' => $request->deployment_type_name,
        ]);

        return response()->json(['message' => 'Deployment Type updated successfully.', 'data' => $deploymentType]);
    }

    public function destroy($id)
    {
        $deploymentType = DeploymentTypeModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $deploymentType->delete();

        return response()->json(['message' => 'Deployment Type deleted successfully.']);
    }
}

