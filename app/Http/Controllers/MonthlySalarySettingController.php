<?php

namespace App\Http\Controllers;

use App\Models\MonthlySalarySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MonthlySalarySettingController extends Controller
{
    public function index(Request $request)
    {
        $query = MonthlySalarySetting::where('user_id', Auth::id());

        // Apply filters
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        // removed is_active filter

        $settings = $query->orderBy('year', 'desc')
                         ->orderBy('month', 'desc')
                         ->paginate(15);

        // Get available years for filter
        $availableYears = MonthlySalarySetting::where('user_id', Auth::id())
            ->distinct()
            ->pluck('year')
            ->sort()
            ->values();

        return view('content.hr.monthly-salary-settings', compact('settings', 'availableYears'));
    }

    public function create()
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = Carbon::createFromDate(null, $i, 1)->format('F');
        }

        $years = range(date('Y') - 5, date('Y') + 5);
        $currentYear = date('Y');

        return view('content.hr.monthly-salary-settings-create', compact('months', 'years', 'currentYear'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'total_working_days' => 'required|integer|min:1|max:31',
            'official_holidays' => 'nullable|integer|min:0|max:31',
            'attendance_rules' => 'nullable|array',
            'overtime_rules' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
            'default_overtime_rate' => 'nullable|numeric|min:0',
        ]);

        // Check if setting already exists for this year and month
        if (MonthlySalarySetting::existsForYearMonth($request->year, $request->month, Auth::id())) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Settings for ' . Carbon::createFromDate($request->year, $request->month, 1)->format('F Y') . ' already exist.');
        }

        $setting = MonthlySalarySetting::create([
            'year' => $request->year,
            'month' => $request->month,
            'total_working_days' => $request->total_working_days,
            'official_holidays' => $request->official_holidays ?? 0,
            'attendance_rules' => $request->attendance_rules ?? MonthlySalarySetting::getDefaultAttendanceRules(),
            'overtime_rules' => $request->overtime_rules ?? MonthlySalarySetting::getDefaultOvertimeRules(),
            'notes' => $request->notes,
            'default_overtime_rate' => $request->default_overtime_rate ?? 0,
            'user_id' => Auth::id(),
        ]);

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Monthly salary settings created successfully.',
                'setting' => $setting,
            ]);
        }

        return redirect()->route('monthly-salary-settings.index')
            ->with('success', 'Monthly salary settings created successfully.');
    }

    public function show(MonthlySalarySetting $monthlySalarySetting)
    {
        // Check if user owns this setting
        if ($monthlySalarySetting->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }
        
        return view('content.hr.monthly-salary-settings-show', compact('monthlySalarySetting'));
    }

    public function edit(MonthlySalarySetting $monthlySalarySetting)
    {
        // Check if user owns this setting
        if ($monthlySalarySetting->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = Carbon::createFromDate(null, $i, 1)->format('F');
        }

        $years = range(date('Y') - 5, date('Y') + 5);

        return view('content.hr.monthly-salary-settings-edit', compact('monthlySalarySetting', 'months', 'years'));
    }

    public function update(Request $request, MonthlySalarySetting $monthlySalarySetting)
    {
        // Check if user owns this setting
        if ($monthlySalarySetting->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'total_working_days' => 'required|integer|min:1|max:31',
            'official_holidays' => 'nullable|integer|min:0|max:31',
            'attendance_rules' => 'nullable|array',
            'overtime_rules' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
            'default_overtime_rate' => 'nullable|numeric|min:0',
        ]);

        // Check if another setting exists for the same year and month (excluding current)
        $existingSetting = MonthlySalarySetting::where('user_id', Auth::id())
            ->where('year', $request->year)
            ->where('month', $request->month)
            ->where('id', '!=', $monthlySalarySetting->id)
            ->first();

        if ($existingSetting) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Settings for ' . Carbon::createFromDate($request->year, $request->month, 1)->format('F Y') . ' already exist.');
        }

        $monthlySalarySetting->update([
            'year' => $request->year,
            'month' => $request->month,
            'total_working_days' => $request->total_working_days,
            'official_holidays' => $request->official_holidays ?? 0,
            'attendance_rules' => $request->attendance_rules ?? MonthlySalarySetting::getDefaultAttendanceRules(),
            'overtime_rules' => $request->overtime_rules ?? MonthlySalarySetting::getDefaultOvertimeRules(),
            'notes' => $request->notes,
            'default_overtime_rate' => $request->default_overtime_rate ?? 0,
        ]);

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Monthly salary settings updated successfully.',
                'setting' => $monthlySalarySetting->fresh(),
            ]);
        }

        return redirect()->route('monthly-salary-settings.index')
            ->with('success', 'Monthly salary settings updated successfully.');
    }

    public function destroy(MonthlySalarySetting $monthlySalarySetting)
    {
        // Check if user owns this setting
        if ($monthlySalarySetting->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        $monthlySalarySetting->delete();

        if (request()->ajax() || request()->wantsJson() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Monthly salary settings deleted successfully.'
            ]);
        }

        return redirect()->route('monthly-salary-settings.index')
            ->with('success', 'Monthly salary settings deleted successfully.');
    }

    public function createYearly()
    {
        $years = range(date('Y') - 5, date('Y') + 5);
        $currentYear = date('Y');

        return view('content.hr.monthly-salary-settings-yearly', compact('years', 'currentYear'));
    }

    public function storeYearly(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'default_working_days' => 'required|integer|min:1|max:31',
            'default_holidays' => 'nullable|integer|min:0|max:31',
            'overtime_rate' => 'nullable|numeric|min:0',
            'attendance_rules' => 'nullable|array',
            'overtime_rules' => 'nullable|array',
        ]);

        // Check if any settings already exist for this year
        if (MonthlySalarySetting::where('user_id', Auth::id())->where('year', $request->year)->exists()) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Settings for year ' . $request->year . ' already exist. Please delete existing settings first or choose a different year.'
                ], 400);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'Settings for year ' . $request->year . ' already exist. Please delete existing settings first or choose a different year.');
        }

        $additionalData = [
            'overtime_rate' => $request->overtime_rate ?? 0,
        ];

        $result = MonthlySalarySetting::createYearlySettings(
            $request->year,
            $request->default_working_days,
            $request->default_holidays ?? 0,
            Auth::id(),
            $additionalData
        );

        if ($result) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Yearly salary settings for ' . $request->year . ' created successfully.'
                ]);
            }
            return redirect()->route('monthly-salary-settings.index')
                ->with('success', 'Yearly salary settings for ' . $request->year . ' created successfully.');
        }

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to create yearly settings. Please try again.'
            ], 500);
        }
        return redirect()->back()
            ->withInput()
            ->with('error', 'Failed to create yearly settings. Please try again.');
    }

    public function calculateSalary(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'basic_salary' => 'required|numeric|min:0',
            'actual_present_days' => 'required|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
        ]);

        $setting = MonthlySalarySetting::where('user_id', Auth::id())
            ->where('year', $request->year)
            ->where('month', $request->month)
            ->first();

        if (!$setting) {
            return response()->json([
                'error' => 'No salary settings found for ' . Carbon::createFromDate($request->year, $request->month, 1)->format('F Y')
            ], 404);
        }

        $calculation = $setting->calculateMonthlySalary(
            $request->basic_salary,
            $request->actual_present_days,
            $request->overtime_hours ?? 0,
            $request->deductions ?? 0
        );

        return response()->json([
            'setting' => $setting,
            'calculation' => $calculation
        ]);
    }

    // removed toggleStatus as is_active is no longer used

    /**
     * Get yearly settings management interface
     */
    public function yearlyManagement()
    {
        $years = range(date('Y') - 5, date('Y') + 5);
        $currentYear = date('Y');

        return view('content.hr.monthly-salary-settings-yearly-management', compact('years', 'currentYear'));
    }

    /**
     * Get settings for a specific year (AJAX)
     */
    public function getYearlySettings(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030'
        ]);

        $settings = MonthlySalarySetting::where('user_id', Auth::id())
            ->where('year', $request->year)
            ->orderBy('month')
            ->get();

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthName = Carbon::createFromDate($request->year, $i, 1)->format('F');
            $totalDays = Carbon::createFromDate($request->year, $i, 1)->daysInMonth;
            
            $existingSetting = $settings->where('month', $i)->first();
            
            $months[] = [
                'month' => $i,
                'month_name' => $monthName,
                'total_days' => $totalDays,
                'setting' => $existingSetting ? [
                    'id' => $existingSetting->id,
                    'total_working_days' => $existingSetting->total_working_days,
                    'official_holidays' => $existingSetting->official_holidays,
                    'attendance_rules' => $existingSetting->attendance_rules,
                    'overtime_rules' => $existingSetting->overtime_rules,
                    'notes' => $existingSetting->notes,
                    'default_overtime_rate' => $existingSetting->default_overtime_rate,
                ] : null
            ];
        }

        return response()->json([
            'year' => $request->year,
            'months' => $months
        ]);
    }

    /**
     * Update or create monthly setting (AJAX)
     */
    public function updateMonthlySetting(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'total_working_days' => 'required|integer|min:1|max:31',
            'official_holidays' => 'nullable|integer|min:0|max:31',
            'attendance_rules' => 'nullable|array',
            'overtime_rules' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
            'default_overtime_rate' => 'nullable|numeric|min:0',
        ]);

        $setting = MonthlySalarySetting::where('user_id', Auth::id())
            ->where('year', $request->year)
            ->where('month', $request->month)
            ->first();

        $data = [
            'year' => $request->year,
            'month' => $request->month,
            'total_working_days' => $request->total_working_days,
            'official_holidays' => $request->official_holidays ?? 0,
            'attendance_rules' => $request->attendance_rules ?? MonthlySalarySetting::getDefaultAttendanceRules(),
            'overtime_rules' => $request->overtime_rules ?? MonthlySalarySetting::getDefaultOvertimeRules(),
            'notes' => $request->notes,
            'default_overtime_rate' => $request->default_overtime_rate ?? 0,
            'user_id' => Auth::id(),
        ];

        if ($setting) {
            $setting->update($data);
            $message = 'Monthly setting updated successfully.';
        } else {
            $setting = MonthlySalarySetting::create($data);
            $message = 'Monthly setting created successfully.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'setting' => $setting
        ]);
    }

    /**
     * Create all 12 months for a year (AJAX)
     */
    public function createYearlySettingsAjax(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'default_working_days' => 'required|integer|min:1|max:31',
            'default_holidays' => 'nullable|integer|min:0|max:31',
            'overtime_rate' => 'nullable|numeric|min:0',
        ]);

        // Check if any settings already exist for this year
        if (MonthlySalarySetting::where('user_id', Auth::id())->where('year', $request->year)->exists()) {
            return response()->json([
                'success' => false,
                'error' => 'Settings for year ' . $request->year . ' already exist. Please delete existing settings first or choose a different year.'
            ], 400);
        }

        $additionalData = [
            'overtime_rate' => $request->overtime_rate ?? 0,
        ];

        $result = MonthlySalarySetting::createYearlySettings(
            $request->year,
            $request->default_working_days,
            $request->default_holidays ?? 0,
            Auth::id(),
            $additionalData
        );

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Yearly salary settings for ' . $request->year . ' created successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Failed to create yearly settings. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete monthly setting (AJAX)
     */
    public function deleteMonthlySetting(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12'
        ]);

        $setting = MonthlySalarySetting::where('user_id', Auth::id())
            ->where('year', $request->year)
            ->where('month', $request->month)
            ->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'error' => 'Setting not found.'
            ], 404);
        }

        $setting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Monthly setting deleted successfully.'
        ]);
    }

    /**
     * AJAX Store method for creating new salary settings
     */
    public function ajaxStore(Request $request)
    {
        try {
            $request->validate([
                'year' => 'required|integer|min:2020|max:2030',
                'month' => 'required|integer|min:1|max:12',
                'total_working_days' => 'required|integer|min:1|max:31',
                'official_holidays' => 'nullable|integer|min:0|max:31',
                'attendance_rules' => 'nullable|array',
                'overtime_rules' => 'nullable|array',
                'notes' => 'nullable|string|max:1000',
                'default_overtime_rate' => 'nullable|numeric|min:0',
                'is_active' => 'boolean',
            ]);

            // Check if setting already exists for this year and month
            if (MonthlySalarySetting::existsForYearMonth($request->year, $request->month, Auth::id())) {
                return response()->json([
                    'success' => false,
                    'error' => 'Settings for ' . Carbon::createFromDate($request->year, $request->month, 1)->format('F Y') . ' already exist.'
                ], 400);
            }

            $setting = MonthlySalarySetting::create([
                'year' => $request->year,
                'month' => $request->month,
                'total_working_days' => $request->total_working_days,
                'official_holidays' => $request->official_holidays ?? 0,
                'attendance_rules' => $request->attendance_rules ?? MonthlySalarySetting::getDefaultAttendanceRules(),
                'overtime_rules' => $request->overtime_rules ?? MonthlySalarySetting::getDefaultOvertimeRules(),
                'notes' => $request->notes,
                'default_overtime_rate' => $request->default_overtime_rate ?? 0,
                'is_active' => $request->has('is_active'),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Monthly salary settings created successfully.',
                'setting' => $setting
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while creating the setting: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX Edit method for getting setting data
     */
    public function ajaxEdit(MonthlySalarySetting $monthlySalarySetting)
    {
        // Check if user owns this setting
        if ($monthlySalarySetting->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access.'
            ], 403);
        }

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = Carbon::createFromDate(null, $i, 1)->format('F');
        }

        $years = range(date('Y') - 5, date('Y') + 5);

        return response()->json([
            'success' => true,
            'setting' => $monthlySalarySetting,
            'months' => $months,
            'years' => $years
        ]);
    }

    /**
     * AJAX Update method for updating salary settings
     */
    public function ajaxUpdate(Request $request, MonthlySalarySetting $monthlySalarySetting)
    {
        try {
            // Check if user owns this setting
            if ($monthlySalarySetting->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access.'
                ], 403);
            }

            $request->validate([
                'year' => 'required|integer|min:2020|max:2030',
                'month' => 'required|integer|min:1|max:12',
                'total_working_days' => 'required|integer|min:1|max:31',
                'official_holidays' => 'nullable|integer|min:0|max:31',
                'attendance_rules' => 'nullable|array',
                'overtime_rules' => 'nullable|array',
                'notes' => 'nullable|string|max:1000',
                'default_overtime_rate' => 'nullable|numeric|min:0',
                'is_active' => 'boolean',
            ]);

            // Check if another setting exists for the same year and month (excluding current)
            $existingSetting = MonthlySalarySetting::where('user_id', Auth::id())
                ->where('year', $request->year)
                ->where('month', $request->month)
                ->where('id', '!=', $monthlySalarySetting->id)
                ->first();

            if ($existingSetting) {
                return response()->json([
                    'success' => false,
                    'error' => 'Settings for ' . Carbon::createFromDate($request->year, $request->month, 1)->format('F Y') . ' already exist.'
                ], 400);
            }

            $monthlySalarySetting->update([
                'year' => $request->year,
                'month' => $request->month,
                'total_working_days' => $request->total_working_days,
                'official_holidays' => $request->official_holidays ?? 0,
                'attendance_rules' => $request->attendance_rules ?? MonthlySalarySetting::getDefaultAttendanceRules(),
                'overtime_rules' => $request->overtime_rules ?? MonthlySalarySetting::getDefaultOvertimeRules(),
                'notes' => $request->notes,
                'default_overtime_rate' => $request->default_overtime_rate ?? 0,
                'is_active' => $request->has('is_active'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Monthly salary settings updated successfully.',
                'setting' => $monthlySalarySetting->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while updating the setting: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX Destroy method for deleting salary settings
     */
    public function ajaxDestroy(MonthlySalarySetting $monthlySalarySetting)
    {
        try {
            // Check if user owns this setting
            if ($monthlySalarySetting->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access.'
                ], 403);
            }

            $monthlySalarySetting->delete();

            return response()->json([
                'success' => true,
                'message' => 'Monthly salary settings deleted successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting the setting: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX Toggle Status method for toggling setting status
     */
    public function ajaxToggleStatus(MonthlySalarySetting $monthlySalarySetting)
    {
        try {
            // Check if user owns this setting
            if ($monthlySalarySetting->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access.'
                ], 403);
            }

            $monthlySalarySetting->update([
                'is_active' => !$monthlySalarySetting->is_active
            ]);

            $status = $monthlySalarySetting->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "Monthly salary settings {$status} successfully.",
                'setting' => $monthlySalarySetting->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while toggling the setting status: ' . $e->getMessage()
            ], 500);
        }
    }
}