<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier as SupplierModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Supplier extends Controller
{
    public function index()
    {
        return view('content.supplier.index');
    }

    public function getSupplier(Request $request)
    {
        $suppliers = SupplierModel::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $suppliers,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_name' => 'required|string|max:255|unique:suppliers,supplier_name,NULL,id,user_id,' . Auth::id(),
            'address' => 'nullable|string',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_mobile' => 'nullable|string|max:20',
            'contact_person_email' => 'nullable|email|max:255',
            'working_experience' => 'nullable|string',
            'joining_date' => 'nullable|date',
            'trade_license_number' => 'nullable|string|max:255',
            'trade_license_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $user = Auth::user();
        $userId = $user->id;
        
        $data = $request->all();
        $data['user_id'] = $userId;

        // Handle file upload
        if ($request->hasFile('trade_license_picture')) {
            $file = $request->file('trade_license_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('suppliers/trade_licenses', $filename, 'public');
            $data['trade_license_picture'] = $path;
        }
        
        $supplier = SupplierModel::create($data);
        
        return response()->json(['message' => 'Supplier created successfully.', 'data' => $supplier], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'supplier_name' => 'required|string|max:255|unique:suppliers,supplier_name,' . $id . ',id,user_id,' . Auth::id(),
            'address' => 'nullable|string',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_mobile' => 'nullable|string|max:20',
            'contact_person_email' => 'nullable|email|max:255',
            'working_experience' => 'nullable|string',
            'joining_date' => 'nullable|date',
            'trade_license_number' => 'nullable|string|max:255',
            'trade_license_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $supplier = SupplierModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        
        $data = $request->all();

        // Handle file upload
        if ($request->hasFile('trade_license_picture')) {
            // Delete old file if exists
            if ($supplier->trade_license_picture && Storage::disk('public')->exists($supplier->trade_license_picture)) {
                Storage::disk('public')->delete($supplier->trade_license_picture);
            }
            
            $file = $request->file('trade_license_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('suppliers/trade_licenses', $filename, 'public');
            $data['trade_license_picture'] = $path;
        }
        
        $supplier->update($data);

        return response()->json(['message' => 'Supplier updated successfully.', 'data' => $supplier]);
    }

    public function destroy($id)
    {
        $supplier = SupplierModel::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        
        // Delete associated file if exists
        if ($supplier->trade_license_picture && Storage::disk('public')->exists($supplier->trade_license_picture)) {
            Storage::disk('public')->delete($supplier->trade_license_picture);
        }
        
        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted successfully.']);
    }
}
