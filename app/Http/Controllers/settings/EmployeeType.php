<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\EmployeeType as EmployeeTypeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EmployeeType extends Controller
{
    public function index()
    {
        return view('content.settings.employee-type');
    }

    public function getEmployeeType(Request $request)
    {
        $employeeTypes = EmployeeTypeModel::all();

        return response()->json([
            'data' => $employeeTypes
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_type_name' => 'required|string|max:255|unique:employee_types,employee_type_name,NULL,id,user_id,' . Auth::id(),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $employeeType = EmployeeTypeModel::create([
            'employee_type_name' => $request->employee_type_name,
            'user_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Employee Type created successfully.', 'data' => $employeeType]);
    }

    public function update(Request $request, $id)
    {
        $employeeType = EmployeeTypeModel::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'employee_type_name' => 'required|string|max:255|unique:employee_types,employee_type_name,' . $id . ',id,user_id,' . Auth::id(),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $employeeType->update([
            'employee_type_name' => $request->employee_type_name,
        ]);

        return response()->json(['message' => 'Employee Type updated successfully.', 'data' => $employeeType]);
    }

    public function destroy($id)
    {
        $employeeType = EmployeeTypeModel::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $employeeType->delete();

        return response()->json(['message' => 'Employee Type deleted successfully.']);
    }
}
