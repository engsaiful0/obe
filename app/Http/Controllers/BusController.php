<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Bus;
use App\Models\BusHelper;
use App\Models\BusSubType;
use App\Models\BusType;
use App\Models\Color;
use App\Models\Driver;
use App\Models\DriverHelperAssignment;
use App\Models\FuelType;
use App\Models\Status as StatusModel;
use App\Models\Supplier;
use App\Models\Year;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getBusesBySubType(Request $request)
    {
        $busActiveStatus = StatusModel::where('related_to', 'bus')
            ->where('status_name', 'like', '%active%')
            ->first();

        $buses = Bus::with('busSubType')->where('status_id', $busActiveStatus->id)->where('bus_sub_type_id', $request->bus_sub_type_id)->get();
        return response()->json([
            'success' => true,
            'buses' => $buses
        ]);
    }

    public function getBusesByType(Request $request)
    {
        $busActiveStatus = StatusModel::where('related_to', 'bus')
            ->where('status_name', 'like', '%active%')
            ->first();
        $buses = Bus::with('busType')->where('status_id', $busActiveStatus->id)->where('bus_type_id', $request->bus_type_id)->get();
        return response()->json([
            'success' => true,
            'buses' => $buses
        ]);
    }

    public function getBusesNamesByTypeAndSubType(Request $request)
    {
        $busActiveStatus = StatusModel::where('related_to', 'bus')
            ->where('status_name', 'like', '%active%')
            ->first();

        try {
            $query = Bus::with('busType', 'busSubType')
                ->where('status_id', $busActiveStatus->id);

            // Add type filter if provided
            if ($request->has('bus_type_id') && $request->bus_type_id) {
                $query->where('bus_type_id', $request->bus_type_id);
            }

            // Add sub type filter if provided
            if ($request->has('bus_sub_type_id') && $request->bus_sub_type_id) {
                $query->where('bus_sub_type_id', $request->bus_sub_type_id);
            }

            $buses = $query->get();

            return response()->json([
                'success' => true,
                'buses' => $buses,
                'count' => $buses->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading buses: ' . $e->getMessage(),
                'buses' => []
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $query = Bus::with([
            'busType',
            'busSubType',
            'brand',
            'statusOptions',
            'yearOfManufacture',
            'color',
            'supplier',
            'fuelType',
            'driver',
            'busHelper'
        ]);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q
                    ->where('model_name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('chassis_number', 'like', "%{$search}%")
                    ->orWhere('engine_number', 'like', "%{$search}%")
                    ->orWhereHas('brand', function ($brandQuery) use ($search) {
                        $brandQuery->where('brand_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('busType', function ($typeQuery) use ($search) {
                        $typeQuery->where('bus_type_name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        // Filter by bus type
        if ($request->filled('bus_type_id')) {
            $query->where('bus_type_id', $request->bus_type_id);
        }

        // Filter by bus sub type
        if ($request->filled('bus_sub_type_id')) {
            $query->where('bus_sub_type_id', $request->bus_sub_type_id);
        }

        // Filter by brand
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Filter by color
        if ($request->filled('color_id')) {
            $query->where('color_id', $request->color_id);
        }

        // Filter by year of manufacture
        if ($request->filled('year_of_manufacture_id')) {
            $query->where('year_of_manufacture_id', $request->year_of_manufacture_id);
        }

        // Filter by fuel type
        if ($request->filled('fuel_type_id')) {
            $query->where('fuel_type_id', $request->fuel_type_id);
        }

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by driver
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        // Filter by bus helper
        if ($request->filled('bus_helper_id')) {
            $query->where('bus_helper_id', $request->bus_helper_id);
        }

        // Filter by document status
        if ($request->filled('document_status')) {
            if ($request->document_status === 'expired') {
                $query->where(function ($q) {
                    $q
                        ->where('registration_expiry', '<', now())
                        ->orWhere('insurance_expiry', '<', now())
                        ->orWhere('fitness_expiry', '<', now())
                        ->orWhere('permit_expiry', '<', now());
                });
            } elseif ($request->document_status === 'valid') {
                $query->where(function ($q) {
                    $q
                        ->where('registration_expiry', '>=', now())
                        ->where('insurance_expiry', '>=', now())
                        ->where('fitness_expiry', '>=', now())
                        ->where('permit_expiry', '>=', now());
                });
            }
        }

        // Filter by registration expiry
        if ($request->filled('registration_expiry')) {
            if ($request->registration_expiry === 'expired') {
                $query->where('registration_expiry', '<', now());
            } elseif ($request->registration_expiry === 'expiring_soon') {
                $query
                    ->where('registration_expiry', '<=', now()->addDays(30))
                    ->where('registration_expiry', '>=', now());
            }
        }

        // Filter by insurance expiry
        if ($request->filled('insurance_expiry')) {
            if ($request->insurance_expiry === 'expired') {
                $query->where('insurance_expiry', '<', now());
            } elseif ($request->insurance_expiry === 'expiring_soon') {
                $query
                    ->where('insurance_expiry', '<=', now()->addDays(30))
                    ->where('insurance_expiry', '>=', now());
            }
        }

        // Filter by fitness expiry
        if ($request->filled('fitness_expiry')) {
            if ($request->fitness_expiry === 'expired') {
                $query->where('fitness_expiry', '<', now());
            } elseif ($request->fitness_expiry === 'expiring_soon') {
                $query
                    ->where('fitness_expiry', '<=', now()->addDays(30))
                    ->where('fitness_expiry', '>=', now());
            }
        }

        // Filter by registration date range
        if ($request->filled('registration_date_from')) {
            $query->whereDate('registration_date', '>=', $request->registration_date_from);
        }

        if ($request->filled('registration_date_to')) {
            $query->whereDate('registration_date', '<=', $request->registration_date_to);
        }

        // Filter by purchase date range
        if ($request->filled('purchase_date_from')) {
            $query->whereDate('purchase_date', '>=', $request->purchase_date_from);
        }

        if ($request->filled('purchase_date_to')) {
            $query->whereDate('purchase_date', '<=', $request->purchase_date_to);
        }

        $buses = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get filter options
        $busTypes = BusType::all();
        $busSubTypes = BusSubType::all();
        $statusOptions = StatusModel::where('related_to', 'bus')->get();
        $brands = Brand::all();
        $colors = Color::all();
        $years = Year::all();
        $fuelTypes = FuelType::all();
        $suppliers = Supplier::all();
        $drivers = Driver::where('status', 'active')->orderBy('full_name')->get();
        $busHelpers = BusHelper::orderBy('bus_helper_name')->get();

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('content.buses.partials.table', compact('buses'))->render(),
                'pagination' => $buses->appends($request->query())->links()->toHtml()
            ]);
        }

        return view('content.buses.index', compact(
            'buses',
            'busTypes',
            'statusOptions',
            'busSubTypes',
            'brands',
            'colors',
            'years',
            'fuelTypes',
            'suppliers',
            'drivers',
            'busHelpers',
            'statusOptions'
        ));
    }

    /**
     * Export buses to Excel/CSV
     */
    public function export(Request $request)
    {
        $query = Bus::with([
            'busType',
            'status',
            'brand',
            'yearOfManufacture',
            'color',
            'supplier',
            'fuelType',
            'statusOptions',
            'driver',
            'busHelper'
        ]);

        // Apply the same filters as index method
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q
                    ->where('model_name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('chassis_number', 'like', "%{$search}%")
                    ->orWhere('engine_number', 'like', "%{$search}%")
                    ->orWhereHas('busType', function ($typeQuery) use ($search) {
                        $typeQuery->where('bus_type_name', 'like', "%{$search}%");
                    });
            });
        }

        // Apply all other filters (same as index method)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('bus_type_id')) {
            $query->where('bus_type_id', $request->bus_type_id);
        }
        if ($request->filled('bus_sub_type_id')) {
            $query->where('bus_sub_type_id', $request->bus_sub_type_id);
        }
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->filled('color_id')) {
            $query->where('color_id', $request->color_id);
        }
        if ($request->filled('year_of_manufacture_id')) {
            $query->where('year_of_manufacture_id', $request->year_of_manufacture_id);
        }
        if ($request->filled('fuel_type_id')) {
            $query->where('fuel_type_id', $request->fuel_type_id);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }
        if ($request->filled('bus_helper_id')) {
            $query->where('bus_helper_id', $request->bus_helper_id);
        }

        $buses = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV content
        $csvData = [];
        $csvData[] = [
            'ID',
            'Model Name',
            'Registration Number',
            'Chassis Number',
            'Engine Number',
            'Bus Type',
            'Brand',
            'Color',
            'Year',
            'Fuel Type',
            'Status',
            'Owner/Supplier',
            'Driver',
            'Bus Helper',
            'Registration Date',
            'Purchase Date',
            'Current Mileage',
            'Fixed Price',
            'Rate Per KM'
        ];

        foreach ($buses as $bus) {
            $csvData[] = [
                $bus->id,
                $bus->model_name,
                $bus->bus_number,
                $bus->registration_number,
                $bus->chassis_number,
                $bus->engine_number,
                $bus->busType->bus_type_name ?? 'N/A',
                $bus->brand->brand_name ?? 'N/A',
                $bus->color->color_name ?? 'N/A',
                $bus->yearOfManufacture->year_name ?? 'N/A',
                $bus->fuelType->fuel_type_name ?? 'N/A',
                ucfirst(str_replace('_', ' ', $bus->status)),
                $bus->supplier->supplier_name ?? 'N/A',
                $bus->driver->full_name ?? 'N/A',
                $bus->busHelper->bus_helper_name ?? 'N/A',
                $bus->registration_date ? \Carbon\Carbon::parse($bus->registration_date)->format('Y-m-d') : 'N/A',
                $bus->purchase_date ? \Carbon\Carbon::parse($bus->purchase_date)->format('Y-m-d') : 'N/A',
                $bus->current_mileage ?? 'N/A',
                $bus->fixed_price ?? 'N/A',
                $bus->statusOptions->status_name ?? 'N/A',
                $bus->rate_per_km ?? 'N/A'
            ];
        }

        $filename = 'buses_export_' . date('Y-m-d_H-i-s') . '.csv';

        $callback = function () use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $busTypes = BusType::all();
        $busSubTypes = BusSubType::all();
        $statusOptions = StatusModel::where('related_to', 'bus')->get();
        $brands = Brand::all();
        $years = Year::all();
        $colors = Color::all();
        $suppliers = Supplier::all();
        $fuelTypes = FuelType::all();
        $drivers = Driver::where('status', 'active')->orderBy('full_name')->get();
        $busHelpers = BusHelper::orderBy('bus_helper_name')->get();

        $transmissionOptions = Bus::getTransmissionOptions();

        return view('content.buses.create', compact(
            'busTypes',
            'busSubTypes',
            'statusOptions',
            'brands',
            'years',
            'colors',
            'suppliers',
            'fuelTypes',
            'drivers',
            'busHelpers',
            'statusOptions',
            'transmissionOptions'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate only required basic info
        $validated = $request->validate([
            'bus_type_id' => 'required|exists:bus_types,id',
            'bus_sub_type_id' => 'required|exists:bus_sub_types,id',
            'model_name' => 'required|string|max:255',
            'bus_number' => 'required|string|max:255',
            'required_oil_per_km' => 'required|numeric|min:0',
            'brand_id' => 'required|exists:brands,id',
            'year_of_manufacture_id' => 'required|exists:years,id',
            'color_id' => 'required|exists:colors,id',
            'fuel_type_id' => 'required|exists:fuel_types,id',
            'chassis_number' => 'required|string|max:255',
            'engine_number' => 'required|string|max:255',
            'status_id' => 'required|exists:statuses,id',
        ]);

        // Optional fields
        $optionalFields = [
            'fixed_price',
            'rate_per_km',
            'registration_number',
            'registration_date',
            'registration_expiry',
            'insurance_number',
            'insurance_company',
            'insurance_expiry',
            'fitness_certificate_number',
            'fitness_expiry',
            'permit_number',
            'permit_expiry',
            'supplier_id',
            'engine_capacity',
            'transmission_type',
            'seating_capacity',
            'gross_weight',
            'bus_length',
            'bus_height',
            'bus_width',
            'purchase_date',
            'status',
            'current_mileage',
            'last_service_date',
            'next_service_due'
        ];

        foreach ($optionalFields as $field) {
            if ($request->has($field)) {
                $validated[$field] = $request->input($field);
            }
        }

        // Handle file uploads
        $fileFields = [
            'bus_photo' => 'buses/photos',
            'registration_document' => 'buses/documents',
            'insurance_document' => 'buses/documents',
            'fitness_certificate' => 'buses/documents',
        ];

        foreach ($fileFields as $field => $path) {
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store($path, 'public');
            }
        }

        $validated['user_id'] = Auth::id();

        $bus = Bus::create($validated);

        // Create or update bus assignment only if at least one of driver_id or bus_helper_id is provided
        $driverId = $validated['driver_id'] ?? null;
        $busHelperId = $validated['bus_helper_id'] ?? null;

        if ($driverId !== null || $busHelperId !== null) {
            DriverHelperAssignment::updateOrCreate(
                ['bus_id' => $bus->id],
                [
                    'driver_id' => $driverId,
                    'bus_helper_id' => $busHelperId,
                    'status_id' => 19,  // Active status related to driver-helper-assignment
                    'assignment_date' => $request->input('assignment_date', now()->toDateString()),
                    'notes' => $request->input('notes'),
                    'user_id' => Auth::id(),
                ]
            );
        } else {
            // If both are null, delete any existing assignment
            DriverHelperAssignment::where('bus_id', $bus->id)->delete();
        }

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Bus created successfully.',
                'data' => $bus->load([
                    'busType',
                    'brand',
                    'yearOfManufacture',
                    'color',
                    'supplier',
                    'fuelType'
                ]),
                'redirect_url' => route('buses.index')
            ]);
        }

        return redirect()
            ->route('buses.index')
            ->with('success', 'Bus created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Bus $bus)
    {
        $bus->load([
            'busType',
            'statusOptions',
            'brand',
            'yearOfManufacture',
            'color',
            'supplier',
            'fuelType',
            'driver',
            'busHelper',
            'driverHelperAssignment.driver',
            'driverHelperAssignment.busHelper',
            'driverHelperAssignment.status'
        ]);

        return view('content.buses.show', compact('bus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bus $bus)
    {
        $busTypes = BusType::all();
        $busSubTypes = BusSubType::all();
        $statusOptions = StatusModel::where('related_to', 'bus')->get();
        $brands = Brand::all();
        $years = Year::all();
        $colors = Color::all();
        $suppliers = Supplier::all();
        $fuelTypes = FuelType::all();
        $drivers = Driver::where('status', 'active')->orderBy('full_name')->get();
        $busHelpers = BusHelper::orderBy('bus_helper_name')->get();

        $transmissionOptions = Bus::getTransmissionOptions();

        return view('content.buses.edit', compact(
            'bus',
            'busTypes',
            'busSubTypes',
            'statusOptions',
            'brands',
            'years',
            'colors',
            'suppliers',
            'fuelTypes',
            'drivers',
            'busHelpers',
            'statusOptions',
            'transmissionOptions'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bus $bus)
    {
        $validated = $request->validate([
            'bus_type_id' => 'required|exists:bus_types,id',
            'bus_sub_type_id' => 'required|exists:bus_sub_types,id',
            'model_name' => 'required|string|max:255',
            'brand_id' => 'required|exists:brands,id',
            'bus_number' => 'required|string|max:255',
            'required_oil_per_km' => 'required|numeric|min:0',
            'year_of_manufacture_id' => 'required|exists:years,id',
            'color_id' => 'required|exists:colors,id',
            'fuel_type_id' => 'required|exists:fuel_types,id',
            'chassis_number' => 'required|string|max:255',
            'engine_number' => 'required|string|max:255',
            'status_id' => 'required|exists:statuses,id',
        ]);

        // Optional fields - merge them into validated data
        $optionalFields = [
            'fixed_price',
            'rate_per_km',
            'registration_number',
            'registration_date',
            'registration_expiry',
            'insurance_number',
            'insurance_company',
            'insurance_expiry',
            'fitness_certificate_number',
            'fitness_expiry',
            'permit_number',
            'permit_expiry',
            'supplier_id',
            'engine_capacity',
            'transmission_type',
            'seating_capacity',
            'gross_weight',
            'bus_length',
            'bus_height',
            'bus_width',
            'purchase_date',
            'status',
            'current_mileage',
            'last_service_date',
            'next_service_due'
        ];

        // Merge optional fields into validated data
        foreach ($optionalFields as $field) {
            if ($request->has($field)) {
                $value = $request->input($field);
                // Convert empty strings to null for optional fields
                $validated[$field] = ($value === '') ? null : $value;
            }
        }

        // Handle file uploads
        if ($request->hasFile('bus_photo')) {
            // Delete old photo if exists
            if ($bus->bus_photo) {
                Storage::disk('public')->delete($bus->bus_photo);
            }
            $validated['bus_photo'] = $request->file('bus_photo')->store('buses/photos', 'public');
        }

        if ($request->hasFile('registration_document')) {
            if ($bus->registration_document) {
                Storage::disk('public')->delete($bus->registration_document);
            }
            $validated['registration_document'] = $request->file('registration_document')->store('buses/documents', 'public');
        }

        if ($request->hasFile('insurance_document')) {
            if ($bus->insurance_document) {
                Storage::disk('public')->delete($bus->insurance_document);
            }
            $validated['insurance_document'] = $request->file('insurance_document')->store('buses/documents', 'public');
        }

        if ($request->hasFile('fitness_certificate')) {
            if ($bus->fitness_certificate) {
                Storage::disk('public')->delete($bus->fitness_certificate);
            }
            $validated['fitness_certificate'] = $request->file('fitness_certificate')->store('buses/documents', 'public');
        }

        $bus->update($validated);

        // Update or create bus assignment only if at least one of driver_id or bus_helper_id is provided
        // Get driver_id and bus_helper_id from request, not validated (they're not in validation rules)
        $driverId = $request->input('driver_id');
        $busHelperId = $request->input('bus_helper_id');

        // Convert empty strings to null
        $driverId = ($driverId === '' || $driverId === null) ? null : $driverId;
        $busHelperId = ($busHelperId === '' || $busHelperId === null) ? null : $busHelperId;

        if ($driverId !== null || $busHelperId !== null) {
            DriverHelperAssignment::updateOrCreate(
                ['bus_id' => $bus->id],
                [
                    'driver_id' => $driverId,
                    'bus_helper_id' => $busHelperId,
                    'status_id' => 19,  // Active status related to driver-helper-assignment
                    'assignment_date' => $request->input('assignment_date', now()->toDateString()),
                    'notes' => $request->input('notes'),
                    'user_id' => Auth::id(),
                ]
            );
        } else {
            // If both are null, delete any existing assignment
            DriverHelperAssignment::where('bus_id', $bus->id)->delete();
        }

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Bus updated successfully.',
                'data' => $bus->load(['busType', 'brand', 'yearOfManufacture', 'color', 'supplier', 'fuelType']),
                'redirect_url' => route('buses.index')
            ]);
        }

        return redirect()
            ->route('buses.index')
            ->with('success', 'Bus updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bus $bus)
    {
        // Delete associated files
        if ($bus->bus_photo) {
            Storage::disk('public')->delete($bus->bus_photo);
        }
        if ($bus->registration_document) {
            Storage::disk('public')->delete($bus->registration_document);
        }
        if ($bus->insurance_document) {
            Storage::disk('public')->delete($bus->insurance_document);
        }
        if ($bus->fitness_certificate) {
            Storage::disk('public')->delete($bus->fitness_certificate);
        }

        $bus->delete();

        return redirect()
            ->route('buses.index')
            ->with('success', 'Bus deleted successfully.');
    }

    /**
     * Get buses with expired documents
     */
    public function expiredDocuments()
    {
        $buses = Bus::with([
            'busType',
            'statusOptions',
            'brand',
            'yearOfManufacture',
            'color',
            'supplier',
            'fuelType'
        ])->get();

        $busesWithExpiredDocs = $buses->filter(function ($bus) {
            return !empty($bus->getExpiredDocuments());
        });

        return view('content.buses.expired-documents', compact('busesWithExpiredDocs'));
    }

    /**
     * Get buses due for service
     */
    public function serviceDue()
    {
        $buses = Bus::with([
            'busType',
            'statusOptions',
            'brand',
            'yearOfManufacture',
            'color',
            'supplier',
            'fuelType'
        ])->get();

        $busesDueForService = $buses->filter(function ($bus) {
            return $bus->isServiceDue();
        });

        return view('content.buses.service-due', compact('busesDueForService'));
    }

    /**
     * Display bus list report
     */
    public function busList(Request $request)
    {
        $query = Bus::with([
            'busType',
            'busSubType',
            'brand',
            'statusOptions',
            'yearOfManufacture',
            'color',
            'supplier',
            'fuelType',
            'driver',
            'busHelper'
        ])->where('user_id', Auth::id());

        // Filter by bus sub type
        if ($request->filled('bus_sub_type_id') && $request->bus_sub_type_id != 'all') {
            $query->where('bus_sub_type_id', $request->bus_sub_type_id);
        }

        // Filter by status
        if ($request->filled('status_id') && $request->status_id != 'all') {
            $query->where('status_id', $request->status_id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q
                    ->where('model_name', 'like', "%{$search}%")
                    ->orWhere('bus_number', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('chassis_number', 'like', "%{$search}%")
                    ->orWhere('engine_number', 'like', "%{$search}%")
                    ->orWhereHas('brand', function ($brandQuery) use ($search) {
                        $brandQuery->where('brand_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('driver', function ($driverQuery) use ($search) {
                        $driverQuery->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        $buses = $query->orderBy('created_at', 'desc')->get();

        // Get bus sub types for filter (filtered by user)
        $busSubTypes = BusSubType::where('user_id', Auth::id())->orderBy('sub_type_name')->get();
        $busStatus = StatusModel::where('related_to', 'bus')->get();

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('content.report.bus-list-table', compact('buses'))->render()
            ]);
        }
        return view('content.report.bus-list', compact('buses', 'busSubTypes', 'busStatus'));
    }

    /**
     * Print bus list
     */
    public function printBusList(Request $request)
    {
        $query = Bus::with([
            'busType',
            'busSubType',
            'brand',
            'statusOptions',
            'yearOfManufacture',
            'color',
            'supplier',
            'fuelType',
            'driver',
            'busHelper'
        ])->where('user_id', Auth::id());

        // Filter by bus sub type
        if ($request->filled('bus_sub_type_id') && $request->bus_sub_type_id != 'all') {
            $query->where('bus_sub_type_id', $request->bus_sub_type_id);
        }

        // Filter by status
        if ($request->filled('status_id') && $request->status_id != 'all') {
            $query->where('status_id', $request->status_id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q
                    ->where('model_name', 'like', "%{$search}%")
                    ->orWhere('bus_number', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('chassis_number', 'like', "%{$search}%")
                    ->orWhere('engine_number', 'like', "%{$search}%")
                    ->orWhereHas('brand', function ($brandQuery) use ($search) {
                        $brandQuery->where('brand_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('driver', function ($driverQuery) use ($search) {
                        $driverQuery->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        $buses = $query->orderBy('created_at', 'desc')->get();

        // Prepare filter information for display
        $filterInfo = [];
        if ($request->filled('bus_sub_type_id') && $request->bus_sub_type_id != 'all') {
            $busSubType = BusSubType::find($request->bus_sub_type_id);
            $filterInfo['bus_sub_type'] = $busSubType ? $busSubType->sub_type_name : 'N/A';
        }
        if ($request->filled('status_id') && $request->status_id != 'all') {
            $status = StatusModel::find($request->status_id);
            $filterInfo['status'] = $status ? ucfirst($status->status_name) : 'N/A';
        }
        if ($request->filled('search')) {
            $filterInfo['search'] = $request->search;
        }

        return view('content.report.bus-list-print-list', compact('buses', 'filterInfo'));
    }

    /**
     * Export bus list to PDF
     */
    public function busListPdf(Request $request)
    {
        $query = Bus::with([
            'busType',
            'busSubType',
            'brand',
            'status',
            'yearOfManufacture',
            'color',
            'supplier',
            'fuelType',
            'driver',
            'busHelper'
        ])->where('user_id', Auth::id());

        // Filter by bus sub type
        if ($request->filled('bus_sub_type_id') && $request->bus_sub_type_id != 'all') {
            $query->where('bus_sub_type_id', $request->bus_sub_type_id);
        }

        // Filter by status
        if ($request->filled('status_id') && $request->status_id != 'all') {
            $query->where('status_id', $request->status_id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q
                    ->where('model_name', 'like', "%{$search}%")
                    ->orWhere('bus_number', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")
                    ->orWhere('chassis_number', 'like', "%{$search}%")
                    ->orWhere('engine_number', 'like', "%{$search}%")
                    ->orWhereHas('brand', function ($brandQuery) use ($search) {
                        $brandQuery->where('brand_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('driver', function ($driverQuery) use ($search) {
                        $driverQuery->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        $buses = $query->orderBy('created_at', 'desc')->get();

        // Get filter information for PDF header
        $filters = [
            'bus_sub_type_id' => $request->bus_sub_type_id,
            'status_id' => $request->status_id,
            'search' => $request->search,
        ];

        // Determine which Bangla font to use (if available)
        $banglaFont = 'DejaVu Sans';
        $useBanglaFont = false;

        if (file_exists(storage_path('fonts/kalpurush.ttf'))) {
            $banglaFont = 'kalpurush';
            $useBanglaFont = true;
        } elseif (file_exists(storage_path('fonts/solaimanlipi.ttf'))) {
            $banglaFont = 'solaimanlipi';
            $useBanglaFont = true;
        } elseif (file_exists(storage_path('fonts/Kalpurush.ttf'))) {
            // Try with capital letter
            $banglaFont = 'Kalpurush';
            $useBanglaFont = true;
        } elseif (file_exists(storage_path('fonts/SolaimanLipi.ttf'))) {
            // Try with capital letter
            $banglaFont = 'SolaimanLipi';
            $useBanglaFont = true;
        }

        // Configure PDF with UTF-8 support for Bangla fonts
        $pdf = Pdf::loadView('content.report.bus-list-pdf', compact('buses', 'filters', 'banglaFont', 'useBanglaFont'));
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('enable-local-file-access', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('defaultFont', $banglaFont);
        $pdf->setOption('isPhpEnabled', true);
        $pdf->setOption('isFontSubsettingEnabled', false);
        $pdf->setOption('isUnicode', true);

        return $pdf->download('bus-list-report-' . date('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Display the assign driver and helper all page
     */
    public function assignDriverHelperAll()
    {
        // Get all own buses with their current assignments
        $buses = Bus::where('bus_sub_type_id', BusSubType::OWN_BUS_SUB_TYPE_ID)
            ->with(['busType', 'busSubType', 'driverHelperAssignment.driver', 'driverHelperAssignment.busHelper', 'driverHelperAssignment.status'])
            ->orderBy('bus_number')
            ->get();

        // Get all drivers
        $drivers = Driver::orderBy('full_name')->get();

        // Get all bus helpers
        $busHelpers = BusHelper::orderBy('bus_helper_name')->get();

        // Get statuses for driver-helper-assignment
        $statuses = StatusModel::where('related_to', 'driver-helper-assignment')->get();

        return view('content.buses.assign-driver-helper-all', compact(
            'buses',
            'drivers',
            'busHelpers',
            'statuses'
        ));
    }

    /**
     * Save a single driver and helper assignment (AJAX)
     */
    public function saveDriverHelperAssignment(Request $request)
    {
        try {
            $validated = $request->validate([
                'bus_id' => 'required|exists:buses,id',
                'driver_id' => 'nullable|exists:drivers,id',
                'bus_helper_id' => 'nullable|exists:bus_helpers,id',
                'assignment_date' => 'required|date',
                'notes' => 'nullable|string|max:1000',
                'status_id' => 'required|exists:statuses,id',
            ]);

            // Verify bus is an own bus
            $bus = Bus::findOrFail($validated['bus_id']);
            if ($bus->bus_sub_type_id != BusSubType::OWN_BUS_SUB_TYPE_ID) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only own buses can have driver and helper assignments.'
                ], 422);
            }

            // Verify status is for driver-helper-assignment
            $status = StatusModel::findOrFail($validated['status_id']);
            if ($status->related_to != 'driver-helper-assignment') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status selected.'
                ], 422);
            }

            // Check if driver is already assigned to another bus
            if (!empty($validated['driver_id'])) {
                $existingDriverAssignment = DriverHelperAssignment::where('driver_id', $validated['driver_id'])
                    ->where('bus_id', '!=', $validated['bus_id'])
                    ->first();
                
                if ($existingDriverAssignment) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This driver is already assigned to another bus.'
                    ], 422);
                }
            }

            // Check if helper is already assigned to another bus
            if (!empty($validated['bus_helper_id'])) {
                $existingHelperAssignment = DriverHelperAssignment::where('bus_helper_id', $validated['bus_helper_id'])
                    ->where('bus_id', '!=', $validated['bus_id'])
                    ->first();
                
                if ($existingHelperAssignment) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This helper is already assigned to another bus.'
                    ], 422);
                }
            }

            // Find or create assignment
            $assignment = DriverHelperAssignment::updateOrCreate(
                ['bus_id' => $validated['bus_id']],
                [
                    'driver_id' => $validated['driver_id'] ?? null,
                    'bus_helper_id' => $validated['bus_helper_id'] ?? null,
                    'status_id' => $validated['status_id'],
                    'assignment_date' => $validated['assignment_date'],
                    'notes' => $validated['notes'] ?? null,
                    'user_id' => Auth::id(),
                ]
            );

            // Update the bus with driver and helper IDs
            $bus->update([
                'driver_id' => $validated['driver_id'] ?? null,
                'bus_helper_id' => $validated['bus_helper_id'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assignment saved successfully.',
                'data' => $assignment->load(['bus', 'driver', 'busHelper', 'status'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save all driver and helper assignments (AJAX)
     */
    public function saveAllDriverHelperAssignments(Request $request)
    {
        try {
            $assignments = $request->input('assignments', []);
            
            if (empty($assignments)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No assignments provided.'
                ], 422);
            }

            // Validate all assignments
            $validatedAssignments = [];
            $driverIds = [];
            $helperIds = [];
            $busIds = [];

            foreach ($assignments as $index => $assignment) {
                $validated = validator($assignment, [
                    'bus_id' => 'required|exists:buses,id',
                    'driver_id' => 'nullable|exists:drivers,id',
                    'bus_helper_id' => 'nullable|exists:bus_helpers,id',
                    'assignment_date' => 'required|date',
                    'notes' => 'nullable|string|max:1000',
                    'status_id' => 'required|exists:statuses,id',
                ])->validate();

                // Check for duplicate bus IDs
                if (in_array($validated['bus_id'], $busIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Bus is duplicated in row " . ($index + 1) . "."
                    ], 422);
                }
                $busIds[] = $validated['bus_id'];

                // Check for duplicate driver IDs
                if (!empty($validated['driver_id'])) {
                    if (in_array($validated['driver_id'], $driverIds)) {
                        return response()->json([
                            'success' => false,
                            'message' => "Driver is assigned to multiple buses in row " . ($index + 1) . "."
                        ], 422);
                    }
                    $driverIds[] = $validated['driver_id'];
                }

                // Check for duplicate helper IDs
                if (!empty($validated['bus_helper_id'])) {
                    if (in_array($validated['bus_helper_id'], $helperIds)) {
                        return response()->json([
                            'success' => false,
                            'message' => "Helper is assigned to multiple buses in row " . ($index + 1) . "."
                        ], 422);
                    }
                    $helperIds[] = $validated['bus_helper_id'];
                }

                $validatedAssignments[] = $validated;
            }

            // Check if any drivers/helpers are already assigned to other buses
            if (!empty($driverIds)) {
                $existingDriverAssignments = DriverHelperAssignment::whereIn('driver_id', $driverIds)
                    ->whereNotIn('bus_id', $busIds)
                    ->pluck('driver_id')
                    ->toArray();
                
                if (!empty($existingDriverAssignments)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Some drivers are already assigned to other buses.'
                    ], 422);
                }
            }

            if (!empty($helperIds)) {
                $existingHelperAssignments = DriverHelperAssignment::whereIn('bus_helper_id', $helperIds)
                    ->whereNotIn('bus_id', $busIds)
                    ->pluck('bus_helper_id')
                    ->toArray();
                
                if (!empty($existingHelperAssignments)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Some helpers are already assigned to other buses.'
                    ], 422);
                }
            }

            // Process all assignments
            $savedAssignments = [];
            DB::beginTransaction();

            try {
                foreach ($validatedAssignments as $assignment) {
                    $bus = Bus::findOrFail($assignment['bus_id']);
                    
                    // Verify bus is an own bus
                    if ($bus->bus_sub_type_id != BusSubType::OWN_BUS_SUB_TYPE_ID) {
                        throw new \Exception('Only own buses can have driver and helper assignments.');
                    }

                    // Verify status is for driver-helper-assignment
                    $status = StatusModel::findOrFail($assignment['status_id']);
                    if ($status->related_to != 'driver-helper-assignment') {
                        throw new \Exception('Invalid status selected.');
                    }

                    // Create or update assignment
                    $assignmentRecord = DriverHelperAssignment::updateOrCreate(
                        ['bus_id' => $assignment['bus_id']],
                        [
                            'driver_id' => $assignment['driver_id'] ?? null,
                            'bus_helper_id' => $assignment['bus_helper_id'] ?? null,
                            'status_id' => $assignment['status_id'],
                            'assignment_date' => $assignment['assignment_date'],
                            'notes' => $assignment['notes'] ?? null,
                            'user_id' => Auth::id(),
                        ]
                    );

                    // Update the bus with driver and helper IDs
                    $bus->update([
                        'driver_id' => $assignment['driver_id'] ?? null,
                        'bus_helper_id' => $assignment['bus_helper_id'] ?? null
                    ]);

                    $savedAssignments[] = $assignmentRecord;
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => count($savedAssignments) . ' assignment(s) saved successfully.',
                    'data' => $savedAssignments
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
