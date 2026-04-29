<?php

namespace App\Http\Controllers;

use App\Http\Controllers\settings\Status;
use App\Models\Driver;
use App\Models\DriverType;
use App\Models\DriverUniqueId;
use App\Models\LicenseType;
use App\Models\Bus;
use App\Models\Religion;
use App\Models\ExperienceYear;
use App\Models\MaritalStatus;
use App\Models\EducationalQualification;
use App\Models\IssuingAuthority;
use App\Models\Status as StatusModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class DriverController extends Controller
{
    /**
     * Get drivers data for AJAX requests
     */
    public function getData(Request $request)
    {
        $drivers = Driver::select('id', 'full_name', 'driver_unique_id')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'data' => $drivers
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Driver::with(['licenseType', 'driverType', 'religion', 'educationalQualification', 'driverStatus', 'experienceYear']);

        // Search by name
        if ($request->filled('search')) {
            $query->where('full_name', 'like', '%' . $request->search . '%');
        }

        // Filter by driver type
        if ($request->filled('driver_type')) {
            $query->where('driver_type_id', $request->driver_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

    

        // Filter by license type
        if ($request->filled('license_type')) {
            $query->where('license_type_id', $request->license_type);
        }

        // Filter by experience
        if ($request->filled('experience')) {
            $query->where('experience_year_id', $request->experience);
        }

        // Filter by salary range
        if ($request->filled('salary_min')) {
            $query->where('gross_salary', '>=', $request->salary_min);
        }
        if ($request->filled('salary_max')) {
            $query->where('gross_salary', '<=', $request->salary_max);
        }

        // Filter by joining date range
        if ($request->filled('joining_date_from')) {
            $query->where('joining_date', '>=', $request->joining_date_from);
        }
        if ($request->filled('joining_date_to')) {
            $query->where('joining_date', '<=', $request->joining_date_to);
        }

        // Filter by license expiry
        if ($request->filled('license_expiry_filter')) {
            $today = now()->toDateString();
            $thirtyDaysFromNow = now()->addDays(30)->toDateString();

            switch ($request->license_expiry_filter) {
                case 'expired':
                    $query->where('license_expiry_date', '<', $today);
                    break;
                case 'expiring_soon':
                    $query->whereBetween('license_expiry_date', [$today, $thirtyDaysFromNow]);
                    break;
                case 'valid':
                    $query->where('license_expiry_date', '>', $thirtyDaysFromNow);
                    break;
            }
        }

        // Sorting - Default to newest first (id desc)
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSortFields = ['id', 'full_name', 'driver_unique_id', 'joining_date', 'gross_salary', 'license_expiry_date'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('id', 'desc');
        }

        $drivers = $query->paginate(50)->withQueryString();

        return view('content.drivers.index', compact('drivers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $licenseTypes = LicenseType::all();
        $driverTypes = DriverType::all();
        $busActiveStatus = StatusModel::where('related_to', 'bus')->where('status_name', 'like', '%active%')
            ->first();
        $buses = Bus::where('status_id', $busActiveStatus->id)->get();

        $experienceOptions = ExperienceYear::all();
        $religions = Religion::all();
        $educationalQualifications = EducationalQualification::all();

        $driverStatusOptions = StatusModel::where('related_to', 'driver')
            ->where('status_name', 'like', '%active%')
            ->get();


        $issuingAuthorities = IssuingAuthority::all();
        $maritalStatuses = MaritalStatus::all();
        
        

        return view('content.drivers.create', compact(
            'licenseTypes',
            'driverTypes',
            'buses',
            'experienceOptions',
            'religions',
            'educationalQualifications',
            'driverStatusOptions',
            'issuingAuthorities',
            'maritalStatuses'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Skip validation and use request data directly
            $data = $request->all();
            $user = Auth::user();
            $userId = $user->id;

            // Handle file uploads
            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('driver_photos', 'public');
            }
            if ($request->hasFile('license_copy')) {
                $data['license_copy'] = $request->file('license_copy')->store('driver_licenses', 'public');
            }
            if ($request->hasFile('nid_copy')) {
                $data['nid_copy'] = $request->file('nid_copy')->store('driver_nids', 'public');
            }
            if ($request->hasFile('police_verification_copy')) {
                $data['police_verification_copy'] = $request->file('police_verification_copy')->store('driver_verifications', 'public');
            }
            if ($request->hasFile('medical_certificate')) {
                $data['medical_certificate'] = $request->file('medical_certificate')->store('driver_medical', 'public');
            }

            $data['user_id'] = $userId;

            // Normalize salary fields based on driver type
            if (!empty($data['driver_type_id'])) {
                $driverType = DriverType::find($data['driver_type_id']);
                if ($driverType) {
                    $type = strtolower($driverType->driver_type_name);

                    $basic = (float)($data['basic_salary'] ?? 0);
                    $house = (float)($data['house_rent'] ?? 0);
                    $medical = (float)($data['medical_allowance'] ?? 0);
                    $other = (float)($data['other_allowance'] ?? 0);
                    $daily = (float)($data['daily_salary'] ?? 0);
                    $food = (float)($data['food_allowance'] ?? 0);

                    if ($type === 'daily') {
                        // Daily: only daily_salary is used
                        $data['daily_salary'] = $daily;
                        $data['basic_salary'] = null;
                        $data['house_rent'] = null;
                        $data['medical_allowance'] = null;
                        $data['other_allowance'] = null;
                        $data['food_allowance'] = null;
                        $data['gross_salary'] = null;
                    } elseif ($type === 'contractual') {
                        // Contractual: basic + food as gross
                        $data['basic_salary'] = $basic;
                        $data['food_allowance'] = $food;
                        $data['daily_salary'] = null;
                        $data['house_rent'] = null;
                        $data['medical_allowance'] = null;
                        $data['other_allowance'] = null;
                        $data['gross_salary'] = $basic + $food;
                    } else {
                        // Regular: basic + house + other as gross
                        $data['basic_salary'] = $basic;
                        $data['house_rent'] = $house;
                        $data['other_allowance'] = $other;
                        $data['daily_salary'] = null;
                        $data['food_allowance'] = null;
                        // medical_allowance can be tracked but not counted in gross per requirement
                        $data['medical_allowance'] = $medical;
                        $data['gross_salary'] = $basic + $house + $other;
                    }
                }
            }

            // Set default status_id if not provided
            if (empty($data['status_id'])) {
                // Get default active status from statuses table for drivers
                $defaultStatus = StatusModel::where(function ($query) {
                    $query->where('related_to', 'driver')
                        ->orWhereNull('related_to');
                })
                    ->where('status_name', 'like', '%active%')
                    ->first();
                if ($defaultStatus) {
                    $data['status_id'] = $defaultStatus->id;
                }
            }

            // Also set the enum status field for backward compatibility
            if (isset($data['status_id'])) {
                $statusRecord = StatusModel::find($data['status_id']);
                if ($statusRecord) {
                    $statusName = strtolower($statusRecord->status_name);
                    if (in_array($statusName, ['active', 'inactive', 'suspended'])) {
                        $data['status'] = $statusName;
                    } else {
                        $data['status'] = 'active';
                    }
                }
            } else {
                $data['status'] = $data['status'] ?? 'active';
            }

            // Create or update driver
            $driver = Driver::updateOrCreate(
                ['id' => $request->id],
                $data
            );

            

            // Handle AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'redirect_url' => route('drivers.index'),
                    'message' => 'Driver saved successfully.',
                    'driver' => $driver->load(['licenseType', 'driverType', 'religion', 'educationalQualification', 'status', 'experienceYear'])
                ]);
            }

            // Redirect to driver index with success message
            return redirect()->route('drivers.index')->with('success', 'Driver saved successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while saving the driver: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'An error occurred while saving the driver: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Driver $driver)
    {
        $driver->load([
            'licenseType', 
            'driverType', 
            'driverUniqueId',
            'religion',
            'educationalQualification',
            'maritalStatus',
            'experienceYear',
            'issuingAuthority',
            'driverStatus'
        ]);
        return view('content.drivers.show', compact('driver'));
    }

    /**
     * Print driver details
     */
    public function print(Driver $driver)
    {
        $driver->load([
            'licenseType', 
            'driverType', 
            'driverUniqueId',
            'religion',
            'educationalQualification',
            'maritalStatus',
            'experienceYear',
            'issuingAuthority',
            'driverStatus'
        ]);
        return view('content.drivers.show', compact('driver'));
    }

    /**
     * Generate PDF for driver details
     */
    public function pdf(Driver $driver)
    {
        $driver->load([
            'licenseType', 
            'driverType', 
            'driverUniqueId',
            'religion',
            'educationalQualification',
            'maritalStatus',
            'experienceYear',
            'issuingAuthority',
            'driverStatus'
        ]);
        
        $pdf = Pdf::loadView('content.drivers.pdf', compact('driver'));
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'driver-' . ($driver->driver_unique_id ?? $driver->id) . '-' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Driver $driver)
    {
        $licenseTypes = LicenseType::all();
        $driverTypes = DriverType::all();
        $busActiveStatus = StatusModel::where('related_to', 'bus')->where('status_name', 'like', '%active%')
            ->first();
        $buses = Bus::where('status_id', $busActiveStatus->id)->get();
        $experienceOptions = ExperienceYear::all();
        $religions = Religion::all();
        $educationalQualifications = EducationalQualification::all();
        $driverStatusOptionActive = StatusModel::where('related_to', 'driver')
            ->where('status_name', 'like', '%active%')
            ->first();
        $drivers = Driver::where('status_id', $driverStatusOptionActive->id)->get();
        $issuingAuthorities = IssuingAuthority::all();
        $maritalStatuses = MaritalStatus::all();


        $driverStatusOptions = StatusModel::where('related_to', 'driver')
            ->where('status_name', 'like', '%active%')
            ->get();

        return view('content.drivers.edit', compact(
            'driver',
            'licenseTypes',
            'driverTypes',
            'buses',
            'experienceOptions',
            'religions',
            'educationalQualifications',
            'driverStatusOptions',
            'issuingAuthorities',
            'maritalStatuses'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Driver $driver)
    {
        // Skip validation and use request data directly
        $data = $request->all();

        // Handle file uploads
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('drivers/photos', 'public');
        }

        if ($request->hasFile('license_copy')) {
            $data['license_copy'] = $request->file('license_copy')->store('drivers/licenses', 'public');
        }

        if ($request->hasFile('nid_copy')) {
            $data['nid_copy'] = $request->file('nid_copy')->store('drivers/nids', 'public');
        }

        if ($request->hasFile('police_verification_copy')) {
            $data['police_verification_copy'] = $request->file('police_verification_copy')->store('drivers/verifications', 'public');
        }

        if ($request->hasFile('medical_certificate')) {
            $data['medical_certificate'] = $request->file('medical_certificate')->store('drivers/medical', 'public');
        }

        // Update enum status field based on status_id for backward compatibility
        if (isset($data['status_id'])) {
            $statusRecord = StatusModel::find($data['status_id']);
            if ($statusRecord) {
                $statusName = strtolower($statusRecord->status_name);
                if (in_array($statusName, ['active', 'inactive', 'suspended'])) {
                    $data['status'] = $statusName;
                } else {
                    $data['status'] = $driver->status ?? 'active';
                }
            }
        }

        // Normalize salary fields based on driver type
        if (!empty($data['driver_type_id'] ?? $driver->driver_type_id)) {
            $driverType = DriverType::find($data['driver_type_id'] ?? $driver->driver_type_id);
            if ($driverType) {
                $type = strtolower($driverType->driver_type_name);

                $basic = (float)($data['basic_salary'] ?? $driver->basic_salary ?? 0);
                $house = (float)($data['house_rent'] ?? $driver->house_rent ?? 0);
                $medical = (float)($data['medical_allowance'] ?? $driver->medical_allowance ?? 0);
                $other = (float)($data['other_allowance'] ?? $driver->other_allowance ?? 0);
                $daily = (float)($data['daily_salary'] ?? $driver->daily_salary ?? 0);
                $food = (float)($data['food_allowance'] ?? $driver->food_allowance ?? 0);

                if ($type === 'daily') {
                    $data['daily_salary'] = $daily;
                    $data['basic_salary'] = null;
                    $data['house_rent'] = null;
                    $data['medical_allowance'] = null;
                    $data['other_allowance'] = null;
                    $data['food_allowance'] = null;
                    $data['gross_salary'] = null;
                } elseif ($type === 'contractual') {
                    $data['basic_salary'] = $basic;
                    $data['food_allowance'] = $food;
                    $data['daily_salary'] = null;
                    $data['house_rent'] = null;
                    $data['medical_allowance'] = null;
                    $data['other_allowance'] = null;
                    $data['gross_salary'] = $basic + $food;
                } else {
                    $data['basic_salary'] = $basic;
                    $data['house_rent'] = $house;
                    $data['other_allowance'] = $other;
                    $data['daily_salary'] = null;
                    $data['food_allowance'] = null;
                    $data['medical_allowance'] = $medical;
                    $data['gross_salary'] = $basic + $house + $other;
                }
            }
        }

        // Update the driver
        $driver->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Driver updated successfully.',
                'redirect_url' => route('drivers.index')
            ]);
        }

        return redirect()->route('drivers.index')->with('success', 'Driver updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Driver $driver)
    {
        // Delete associated files
        $this->deleteFile($driver->photo);
        $this->deleteFile($driver->license_copy);
        $this->deleteFile($driver->nid_copy);
        $this->deleteFile($driver->police_verification_copy);
        $this->deleteFile($driver->medical_certificate);

        $driver->delete();

        return redirect()->route('drivers.index')->with('success', 'Driver deleted successfully.');
    }

    /**
     * Delete file from storage
     */
    private function deleteFile($filePath)
    {
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
    }
}
