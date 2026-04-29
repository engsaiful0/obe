@extends('layouts/layoutMaster')

@section('title', 'Driver List')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">Driver List</h5>
        <div class="d-flex gap-2">
            <button class="btn btn-success" onclick="exportToPDF()">
                <i class="ti ti-file-pdf me-1"></i>Export PDF
            </button>
            <a href="{{ route('drivers.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>Add Driver
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ti ti-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Advanced Filter Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="ti ti-filter me-2"></i>Advanced Filters
                    <button class="btn btn-sm btn-outline-secondary float-end" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false">
                        <i class="ti ti-chevron-down"></i> Toggle Filters
                    </button>
                </h6>
            </div>
            <div class="collapse" id="filterCollapse">
                <div class="card-body">
                    <form method="GET" action="{{ route('drivers.index') }}" id="filterForm">
                        <div class="row g-3">
                            <!-- Search by Name -->
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search by Name</label>
                                <input type="text" name="search" id="search" class="form-control" 
                                       value="{{ request('search') }}" placeholder="Enter driver name...">
                            </div>
                            
                            <!-- Driver Type Filter -->
                            <div class="col-md-3">
                                <label for="driver_type" class="form-label">Driver Type</label>
                                <select name="driver_type" id="driver_type" class="form-select">
                                    <option value="">All Types</option>
                                    @foreach(\App\Models\DriverType::all() as $type)
                                        <option value="{{ $type->id }}" {{ request('driver_type') == $type->id ? 'selected' : '' }}>
                                            {{ $type->driver_type_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Status Filter -->
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                            </div>
                            
                       
                            
                            <!-- License Type Filter -->
                            <div class="col-md-3">
                                <label for="license_type" class="form-label">License Type</label>
                                <select name="license_type" id="license_type" class="form-select">
                                    <option value="">All License Types</option>
                                    @foreach(\App\Models\LicenseType::all() as $type)
                                        <option value="{{ $type->id }}" {{ request('license_type') == $type->id ? 'selected' : '' }}>
                                            {{ $type->license_type_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Experience Filter -->
                            <div class="col-md-3">
                                <label for="experience" class="form-label">Experience</label>
                                <select name="experience" id="experience" class="form-select">
                                    <option value="">All Experience</option>
                                    @foreach(\App\Models\ExperienceYear::all() as $exp)
                                        <option value="{{ $exp->id }}" {{ request('experience') == $exp->id ? 'selected' : '' }}>
                                            {{ $exp->experience_year_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Salary Range Filter -->
                            <div class="col-md-3">
                                <label for="salary_min" class="form-label">Min Salary</label>
                                <input type="number" name="salary_min" id="salary_min" class="form-control" 
                                       value="{{ request('salary_min') }}" placeholder="Minimum salary">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="salary_max" class="form-label">Max Salary</label>
                                <input type="number" name="salary_max" id="salary_max" class="form-control" 
                                       value="{{ request('salary_max') }}" placeholder="Maximum salary">
                            </div>
                            
                            <!-- Date Range Filters -->
                            <div class="col-md-3">
                                <label for="joining_date_from" class="form-label">Joining Date From</label>
                                <input type="date" name="joining_date_from" id="joining_date_from" class="form-control" 
                                       value="{{ request('joining_date_from') }}">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="joining_date_to" class="form-label">Joining Date To</label>
                                <input type="date" name="joining_date_to" id="joining_date_to" class="form-control" 
                                       value="{{ request('joining_date_to') }}">
                            </div>
                            
                            <!-- License Expiry Filter -->
                            <div class="col-md-3">
                                <label for="license_expiry_filter" class="form-label">License Expiry</label>
                                <select name="license_expiry_filter" id="license_expiry_filter" class="form-select">
                                    <option value="">All</option>
                                    <option value="expired" {{ request('license_expiry_filter') == 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="expiring_soon" {{ request('license_expiry_filter') == 'expiring_soon' ? 'selected' : '' }}>Expiring Soon (30 days)</option>
                                    <option value="valid" {{ request('license_expiry_filter') == 'valid' ? 'selected' : '' }}>Valid</option>
                                </select>
                            </div>
                            
                            <!-- Sort Options -->
                            <div class="col-md-3">
                                <label for="sort_by" class="form-label">Sort By</label>
                                <select name="sort_by" id="sort_by" class="form-select">
                                    <option value="id" {{ request('sort_by', 'id') == 'id' ? 'selected' : '' }}>Newest First</option>
                                    <option value="full_name" {{ request('sort_by') == 'full_name' ? 'selected' : '' }}>Name</option>
                                    <option value="driver_unique_id" {{ request('sort_by') == 'driver_unique_id' ? 'selected' : '' }}>Driver ID</option>
                                    <option value="joining_date" {{ request('sort_by') == 'joining_date' ? 'selected' : '' }}>Joining Date</option>
                                    <option value="gross_salary" {{ request('sort_by') == 'gross_salary' ? 'selected' : '' }}>Salary</option>
                                    <option value="license_expiry_date" {{ request('sort_by') == 'license_expiry_date' ? 'selected' : '' }}>License Expiry</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <select name="sort_order" id="sort_order" class="form-select">
                                    <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>Descending</option>
                                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-search me-1"></i>Apply Filters
                                    </button>
                                    <a href="{{ route('drivers.index') }}" class="btn btn-outline-secondary">
                                        <i class="ti ti-refresh me-1"></i>Clear Filters
                                    </a>
                                    <button type="button" class="btn btn-outline-info" onclick="saveFilterPreset()">
                                        <i class="ti ti-bookmark me-1"></i>Save Filter Preset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        @if($drivers->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Photo</th>
                            <th>Full Name</th>
                            <th>Driver ID</th>
                            <th>License Number</th>
                            <th>Driver Type</th>
                            <th>Contact Number</th>
                            
                            <th>Status</th>
                            @if(auth()->user()->hasPermissionTo('driver-view') || auth()->user()->hasPermissionTo('driver-edit') || auth()->user()->hasPermissionTo('driver-delete'))
                            <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($drivers as $index => $driver)
                            <tr>
                                <td>{{ $drivers->firstItem() + $index }}</td>
                                <td>
                                    @if($driver->photo)
                                        <img src="{{ asset('storage/app/public/' . $driver->photo) }}" 
                                             alt="{{ $driver->full_name }}" 
                                             class="rounded-circle" 
                                             width="40" 
                                             height="40"
                                             style="object-fit: cover;">
                                    @else
                                        <div class="avatar avatar-sm bg-secondary rounded-circle d-flex align-items-center justify-content-center">
                                            <span class="text-white">{{ substr($driver->full_name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $driver->full_name }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $driver->driver_unique_id }}</span>
                                </td>
                                <td>{{ $driver->license_number }}</td>
                                <td>{{ $driver->driverType->driver_type_name ?? 'N/A' }}</td>
                                <td>{{ $driver->contact_number }}</td>
                             
                                <td>
                                    @if($driver->status == 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($driver->status == 'inactive')
                                        <span class="badge bg-secondary">Inactive</span>
                                    @else
                                        <span class="badge bg-warning">Suspended</span>
                                    @endif
                                </td>
                                @if(auth()->user()->hasPermissionTo('driver-view') || auth()->user()->hasPermissionTo('driver-edit') || auth()->user()->hasPermissionTo('driver-delete'))
                                <td>
                                    <div class="btn-group" role="group">
                                        @if(auth()->user()->hasPermissionTo('driver-view'))
                                        <a href="{{ route('drivers.show', $driver) }}" 
                                           class="btn btn-sm btn-outline-info">
                                            <i class="ti ti-eye"></i> View
                                        </a>
                                        @endif
                                        @if(auth()->user()->hasPermissionTo('driver-edit'))
                                        <a href="{{ route('drivers.edit', $driver) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('drivers.destroy', $driver) }}" 
                                              method="POST" 
                                              style="display: inline-block;"
                                              onsubmit="return confirm('Are you sure you want to delete this driver?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="ti ti-trash"></i> Delete
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <p class="text-muted mb-0">
                        Showing {{ $drivers->firstItem() }} to {{ $drivers->lastItem() }} 
                        of {{ $drivers->total() }} results
                    </p>
                </div>
                <div>
                    {{ $drivers->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="ti ti-car" style="font-size: 4rem; color: #ccc;"></i>
                </div>
                <h5 class="text-muted">No drivers found</h5>
                <p class="text-muted">Start by adding your first driver.</p>
                <a href="{{ route('drivers.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Add Driver
                </a>
            </div>
        @endif
    </div>
</div>

<!-- PDF Export Modal -->
<div class="modal fade" id="pdfExportModal" tabindex="-1" aria-labelledby="pdfExportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pdfExportModalLabel">
                    <i class="ti ti-file-pdf me-2"></i>Export to PDF
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="pdfExportForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="pdf_title" class="form-label">Report Title</label>
                            <input type="text" class="form-control" id="pdf_title" value="Driver List Report" required>
                        </div>
                        <div class="col-md-6">
                            <label for="pdf_orientation" class="form-label">Page Orientation</label>
                            <select class="form-select" id="pdf_orientation">
                                <option value="portrait">Portrait</option>
                                <option value="landscape">Landscape</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="pdf_page_size" class="form-label">Page Size</label>
                            <select class="form-select" id="pdf_page_size">
                                <option value="A4">A4</option>
                                <option value="A3">A3</option>
                                <option value="Letter">Letter</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="pdf_include_photo" class="form-label">Include Photos</label>
                            <select class="form-select" id="pdf_include_photo">
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <h6>Include Columns:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="col_photo" checked>
                                    <label class="form-check-label" for="col_photo">Photo</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="col_name" checked>
                                    <label class="form-check-label" for="col_name">Name</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="col_id" checked>
                                    <label class="form-check-label" for="col_id">Driver ID</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="col_license" checked>
                                    <label class="form-check-label" for="col_license">License Number</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="col_type" checked>
                                    <label class="form-check-label" for="col_type">Driver Type</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="col_contact" checked>
                                    <label class="form-check-label" for="col_contact">Contact</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="col_vehicle" checked>
                                    <label class="form-check-label" for="col_vehicle">Vehicle</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="col_status" checked>
                                    <label class="form-check-label" for="col_status">Status</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="generatePDF()">
                    <i class="ti ti-download me-1"></i>Generate PDF
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script>
    // PDF Export Functions
    function exportToPDF() {
        $('#pdfExportModal').modal('show');
    }
    
    function generatePDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        // Get form data
        const title = document.getElementById('pdf_title').value;
        const orientation = document.getElementById('pdf_orientation').value;
        const pageSize = document.getElementById('pdf_page_size').value;
        const includePhoto = document.getElementById('pdf_include_photo').value;
        
        // Create new document with specified settings
        const pdf = new jsPDF({
            orientation: orientation,
            unit: 'mm',
            format: pageSize.toLowerCase()
        });
        
        // Add title
        pdf.setFontSize(16);
        pdf.text(title, 14, 20);
        
        // Add date
        pdf.setFontSize(10);
        pdf.text('Generated on: ' + new Date().toLocaleDateString(), 14, 30);
        
        // Prepare table data
        const tableData = [];
        const headers = [];
        
        // Get column selections
        const columns = {
            photo: document.getElementById('col_photo').checked,
            name: document.getElementById('col_name').checked,
            id: document.getElementById('col_id').checked,
            license: document.getElementById('col_license').checked,
            type: document.getElementById('col_type').checked,
            contact: document.getElementById('col_contact').checked,
            vehicle: document.getElementById('col_vehicle').checked,
            status: document.getElementById('col_status').checked
        };
        
        // Build headers
        if (columns.photo) headers.push('Photo');
        if (columns.name) headers.push('Name');
        if (columns.id) headers.push('Driver ID');
        if (columns.license) headers.push('License');
        if (columns.type) headers.push('Type');
        if (columns.contact) headers.push('Contact');
        if (columns.vehicle) headers.push('Vehicle');
        if (columns.status) headers.push('Status');
        
        // Build table data
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach(row => {
            const rowData = [];
            const cells = row.querySelectorAll('td');
            
            if (columns.photo) rowData.push(''); // Photo placeholder
            if (columns.name) rowData.push(cells[2]?.textContent || '');
            if (columns.id) rowData.push(cells[3]?.textContent || '');
            if (columns.license) rowData.push(cells[4]?.textContent || '');
            if (columns.type) rowData.push(cells[5]?.textContent || '');
            if (columns.contact) rowData.push(cells[6]?.textContent || '');
            if (columns.vehicle) rowData.push(cells[7]?.textContent || '');
            if (columns.status) rowData.push(cells[8]?.textContent || '');
            
            tableData.push(rowData);
        });
        
        // Add table
        pdf.autoTable({
            head: [headers],
            body: tableData,
            startY: 40,
            styles: {
                fontSize: 8,
                cellPadding: 3
            },
            headStyles: {
                fillColor: [66, 139, 202],
                textColor: 255
            }
        });
        
        // Save PDF
        pdf.save('drivers-report-' + new Date().toISOString().split('T')[0] + '.pdf');
        
        // Close modal
        $('#pdfExportModal').modal('hide');
    }
    
    // Filter Preset Functions
    function saveFilterPreset() {
        const filterData = {
            search: document.getElementById('search').value,
            driver_type: document.getElementById('driver_type').value,
            status: document.getElementById('status').value,
            
            license_type: document.getElementById('license_type').value,
            experience: document.getElementById('experience').value,
            salary_min: document.getElementById('salary_min').value,
            salary_max: document.getElementById('salary_max').value,
            joining_date_from: document.getElementById('joining_date_from').value,
            joining_date_to: document.getElementById('joining_date_to').value,
            license_expiry_filter: document.getElementById('license_expiry_filter').value,
            sort_by: document.getElementById('sort_by').value,
            sort_order: document.getElementById('sort_order').value
        };
        
        localStorage.setItem('driverFilterPreset', JSON.stringify(filterData));
        alert('Filter preset saved successfully!');
    }
    
    function loadFilterPreset() {
        const savedPreset = localStorage.getItem('driverFilterPreset');
        if (savedPreset) {
            const filterData = JSON.parse(savedPreset);
            
            Object.keys(filterData).forEach(key => {
                const element = document.getElementById(key);
                if (element) {
                    element.value = filterData[key];
                }
            });
        }
    }
    
    // Auto-submit form on filter change
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filterForm');
        const autoSubmitElements = ['driver_type', 'status', 'license_type', 'experience', 'license_expiry_filter', 'sort_by', 'sort_order'];
        
        autoSubmitElements.forEach(elementId => {
            const element = document.getElementById(elementId);
            if (element) {
                element.addEventListener('change', function() {
                    filterForm.submit();
                });
            }
        });
        
        // Load saved preset
        loadFilterPreset();
    });
    
    // Quick filter buttons
    function quickFilter(type) {
        const filterForm = document.getElementById('filterForm');
        
        switch(type) {
            case 'active':
                document.getElementById('status').value = 'active';
                break;
            case 'inactive':
                document.getElementById('status').value = 'inactive';
                break;
            case 'expired_license':
                document.getElementById('license_expiry_filter').value = 'expired';
                break;
            case 'expiring_soon':
                document.getElementById('license_expiry_filter').value = 'expiring_soon';
                break;
           
        }
        
        filterForm.submit();
    }
</script>
@endsection
