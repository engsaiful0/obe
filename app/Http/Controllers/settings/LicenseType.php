<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\LicenseType as LicenseTypeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LicenseType extends Controller
{
    public function index()
    {
        return view('content.settings.license-type');
    }

    public function getLicenseType(Request $request)
    {
        $licenseTypes = LicenseTypeModel::all();

        return response()->json([
            'data' => $licenseTypes
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'license_type_name' => 'required|string|max:255|unique:license_types,license_type_name,NULL,id,user_id,' . Auth::id(),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $licenseType = LicenseTypeModel::create([
            'license_type_name' => $request->license_type_name,
            'user_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'License Type created successfully.', 'data' => $licenseType]);
    }

    public function update(Request $request, $id)
    {
        $licenseType = LicenseTypeModel::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'license_type_name' => 'required|string|max:255|unique:license_types,license_type_name,' . $id . ',id,user_id,' . Auth::id(),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $licenseType->update([
            'license_type_name' => $request->license_type_name,
        ]);

        return response()->json(['message' => 'License Type updated successfully.', 'data' => $licenseType]);
    }

    public function destroy($id)
    {
        $licenseType = LicenseTypeModel::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $licenseType->delete();

        return response()->json(['message' => 'License Type deleted successfully.']);
    }
}
