<?php

namespace App\Http\Controllers\settings;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AppSettings extends Controller
{
    public function index()
    {
        $settings = AppSetting::firstOrCreate([]);
        return view('content.settings.app-settings', compact('settings'));
    }

    public function update(Request $request, $id)
    {
        try {
            $settings = AppSetting::find($id);
            
            if (!$settings) {
                if ($request->ajax()) {
                    return response()->json(['error' => 'Settings not found'], 404);
                }
                return redirect()->back()->with('error', 'Settings not found');
            }

            $validated = $request->validate([
                'university_name' => ['nullable', 'string', 'max:255'],
                'short_name' => ['nullable', 'string', 'max:255'],
                'address' => ['nullable', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:50'],
                'email' => ['nullable', 'email', 'max:255'],
                'website' => ['nullable', 'string', 'max:255'],
                'established_year' => ['nullable', 'integer', 'digits:4'],
                'vc_name' => ['nullable', 'string', 'max:255'],
                'pro_vc_name' => ['nullable', 'string', 'max:255'],
                'registrar_name' => ['nullable', 'string', 'max:255'],
                'controller_name' => ['nullable', 'string', 'max:255'],
                'time_zone' => ['nullable', 'string', 'max:100'],
                'academic_system' => ['nullable', 'in:Semester,Trimester,Yearly'],
                'status' => ['nullable', 'in:Active,Inactive'],
                'logo' => ['nullable', 'image', 'max:2048'],
            ]);

            $data = collect($validated)->except(['logo'])->toArray();
            $user = Auth::user();
            $userId = $user->id;
            $data['user_id'] = $userId;
            
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $logoName = 'logo.' . $logo->getClientOriginalExtension();
                $logo->move(public_path('assets/img/branding'), $logoName);
                $data['logo'] = $logoName;
            }

            $settings->update($data);

            // Return JSON response for AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Settings updated successfully!',
                    'data' => $settings->fresh()
                ]);
            }

            return redirect()->back()->with('success', 'Settings updated successfully');
            
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'An error occurred while updating settings: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while updating settings');
        }
    }
}
