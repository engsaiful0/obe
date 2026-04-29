@extends('layouts/layoutMaster')

@section('title', 'API Documentation - GET Requisitions')

@section('page-style')
<style>
    .api-endpoint {
        background: #f8f9fa;
        border-left: 4px solid #667eea;
        padding: 1rem;
        margin: 1rem 0;
        border-radius: 4px;
    }
    .method-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.875rem;
        margin-right: 0.5rem;
    }
    .method-get {
        background: #007bff;
        color: white;
    }
    .code-block {
        background: #2d2d2d;
        color: #f8f8f2;
        padding: 1.5rem;
        border-radius: 8px;
        overflow-x: auto;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        line-height: 1.6;
    }
    .param-table {
        width: 100%;
        border-collapse: collapse;
        margin: 1rem 0;
    }
    .param-table th,
    .param-table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid #dee2e6;
    }
    .param-table th {
        background: #f8f9fa;
        font-weight: 600;
    }
    .required {
        color: #dc3545;
        font-weight: 600;
    }
    .optional {
        color: #6c757d;
    }
    .response-example {
        margin-top: 1rem;
    }
    .copy-btn {
        float: right;
        margin-top: -0.5rem;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">API Documentation - Get Requisitions (GET)</h4>
        <a href="{{ route('app-bus-requisitions') }}" class="btn btn-label-secondary">
            <i class="ti ti-arrow-left me-1"></i> Back to Requisitions
        </a>
    </div>

    <div class="row">
        <div class="col-12">
            <!-- Endpoint Overview -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Endpoint Overview</h5>
                </div>
                <div class="card-body">
                    <div class="api-endpoint">
                        <span class="method-badge method-get">GET</span>
                        <code>{{ url('/api/bus-requisitions') }}</code>
                    </div>
                    <p class="mt-3">
                        This endpoint allows external portals to retrieve bus requisitions from the system. 
                        You can filter the results by various parameters and paginate through the results.
                    </p>
                </div>
            </div>

            <!-- Query Parameters -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Query Parameters</h5>
                </div>
                <div class="card-body">
                    <p>All parameters are optional and can be used to filter the results.</p>
                    <table class="param-table">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>status</code></td>
                                <td>string</td>
                                <td><span class="optional">No</span></td>
                                <td>Filter by status: <code>pending</code>, <code>approved</code>, or <code>rejected</code></td>
                            </tr>
                            <tr>
                                <td><code>department_id</code></td>
                                <td>integer</td>
                                <td><span class="optional">No</span></td>
                                <td>Filter by department ID</td>
                            </tr>
                            <tr>
                                <td><code>date_from</code></td>
                                <td>string (date)</td>
                                <td><span class="optional">No</span></td>
                                <td>Filter requisitions from this date onwards (format: YYYY-MM-DD)</td>
                            </tr>
                            <tr>
                                <td><code>date_to</code></td>
                                <td>string (date)</td>
                                <td><span class="optional">No</span></td>
                                <td>Filter requisitions up to this date (format: YYYY-MM-DD)</td>
                            </tr>
                            <tr>
                                <td><code>required_bus_date_from</code></td>
                                <td>string (date)</td>
                                <td><span class="optional">No</span></td>
                                <td>Filter by required bus date from (format: YYYY-MM-DD)</td>
                            </tr>
                            <tr>
                                <td><code>required_bus_date_to</code></td>
                                <td>string (date)</td>
                                <td><span class="optional">No</span></td>
                                <td>Filter by required bus date to (format: YYYY-MM-DD)</td>
                            </tr>
                            <tr>
                                <td><code>page</code></td>
                                <td>integer</td>
                                <td><span class="optional">No</span></td>
                                <td>Page number for pagination (default: 1)</td>
                            </tr>
                            <tr>
                                <td><code>per_page</code></td>
                                <td>integer</td>
                                <td><span class="optional">No</span></td>
                                <td>Number of items per page (default: 10)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Request Example -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Request Examples</h5>
                    <button class="btn btn-sm btn-label-secondary copy-btn" onclick="copyToClipboard('request-example')">
                        <i class="ti ti-copy me-1"></i> Copy
                    </button>
                </div>
                <div class="card-body">
                    <h6 class="mb-2">Get All Requisitions:</h6>
                    <div class="code-block" id="request-example">
curl -X GET "{{ url('/api/bus-requisitions') }}" \
  -H "Accept: application/json"
                    </div>

                    <h6 class="mb-2 mt-4">Get Pending Requisitions:</h6>
                    <div class="code-block" id="request-filter-example">
curl -X GET "{{ url('/api/bus-requisitions') }}?status=pending" \
  -H "Accept: application/json"
                    </div>

                    <h6 class="mb-2 mt-4">Get Requisitions with Filters:</h6>
                    <div class="code-block" id="request-advanced-example">
curl -X GET "{{ url('/api/bus-requisitions') }}?status=approved&department_id=1&date_from=2024-01-01&date_to=2024-01-31&per_page=20&page=1" \
  -H "Accept: application/json"
                    </div>

                    <h6 class="mb-2 mt-4">JavaScript (Fetch) Example:</h6>
                    <div class="code-block" id="request-js-example">
// Get all requisitions
fetch('{{ url('/api/bus-requisitions') }}', {
  method: 'GET',
  headers: {
    'Accept': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));

// Get pending requisitions with pagination
const params = new URLSearchParams({
  status: 'pending',
  per_page: 10,
  page: 1
});

fetch(`{{ url('/api/bus-requisitions') }}?${params}`, {
  method: 'GET',
  headers: {
    'Accept': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));
                    </div>

                    <h6 class="mb-2 mt-4">PHP Example:</h6>
                    <div class="code-block" id="request-php-example">
// Get all requisitions
$url = '{{ url('/api/bus-requisitions') }}';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json'
]);
$response = curl_exec($ch);
curl_close($ch);
$result = json_decode($response, true);

// Get filtered requisitions
$params = http_build_query([
    'status' => 'pending',
    'department_id' => 1,
    'per_page' => 20,
    'page' => 1
]);
$url = '{{ url('/api/bus-requisitions') }}?' . $params;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json'
]);
$response = curl_exec($ch);
curl_close($ch);
$result = json_decode($response, true);
                    </div>
                </div>
            </div>

            <!-- Response Example -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Success Response</h5>
                    <button class="btn btn-sm btn-label-secondary copy-btn" onclick="copyToClipboard('response-example')">
                        <i class="ti ti-copy me-1"></i> Copy
                    </button>
                </div>
                <div class="card-body">
                    <p><strong>HTTP Status Code:</strong> <code>200 OK</code></p>
                    <div class="code-block response-example" id="response-example">
{
  "success": true,
  "message": "Bus requisitions retrieved successfully.",
  "data": [
    {
      "id": 123,
      "date": "2024-01-15",
      "purpose": "Transportation for annual conference attendees",
      "required_bus_date": "2024-01-20",
      "required_time": "08:00:00",
      "number_of_buses": 3,
      "total_passengers": 120,
      "department": "IT Department",
      "department_id": 1,
      "requisition_sender_name": "John Doe",
      "mobile_number": "+8801712345678",
      "email_address": "john.doe@example.com",
      "remarks": "Please ensure buses are air-conditioned",
      "status": "pending",
      "created_at": "2024-01-15 10:30:00",
      "updated_at": "2024-01-15 10:30:00"
    },
    {
      "id": 124,
      "date": "2024-01-16",
      "purpose": "Field trip transportation",
      "required_bus_date": "2024-01-25",
      "required_time": "09:00:00",
      "number_of_buses": 2,
      "total_passengers": 80,
      "department": "Academic Department",
      "department_id": 2,
      "requisition_sender_name": "Jane Smith",
      "mobile_number": "+8801712345679",
      "email_address": "jane.smith@example.com",
      "remarks": null,
      "status": "approved",
      "created_at": "2024-01-16 11:00:00",
      "updated_at": "2024-01-16 14:30:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 10,
    "total": 25,
    "last_page": 3,
    "from": 1,
    "to": 10
  }
}
                    </div>
                </div>
            </div>

            <!-- Response Fields -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Response Fields</h5>
                </div>
                <div class="card-body">
                    <table class="param-table">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Type</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>success</code></td>
                                <td>boolean</td>
                                <td>Indicates if the request was successful</td>
                            </tr>
                            <tr>
                                <td><code>message</code></td>
                                <td>string</td>
                                <td>Response message</td>
                            </tr>
                            <tr>
                                <td><code>data</code></td>
                                <td>array</td>
                                <td>Array of requisition objects</td>
                            </tr>
                            <tr>
                                <td><code>data[].id</code></td>
                                <td>integer</td>
                                <td>Unique requisition ID</td>
                            </tr>
                            <tr>
                                <td><code>data[].status</code></td>
                                <td>string</td>
                                <td>Status: pending, approved, or rejected</td>
                            </tr>
                            <tr>
                                <td><code>pagination</code></td>
                                <td>object</td>
                                <td>Pagination information</td>
                            </tr>
                            <tr>
                                <td><code>pagination.current_page</code></td>
                                <td>integer</td>
                                <td>Current page number</td>
                            </tr>
                            <tr>
                                <td><code>pagination.per_page</code></td>
                                <td>integer</td>
                                <td>Items per page</td>
                            </tr>
                            <tr>
                                <td><code>pagination.total</code></td>
                                <td>integer</td>
                                <td>Total number of items</td>
                            </tr>
                            <tr>
                                <td><code>pagination.last_page</code></td>
                                <td>integer</td>
                                <td>Last page number</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notes -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Important Notes</h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li>The API endpoint does not require authentication.</li>
                        <li>All query parameters are optional.</li>
                        <li>Results are paginated by default (10 items per page).</li>
                        <li>You can customize pagination using <code>per_page</code> and <code>page</code> parameters.</li>
                        <li>Results are sorted by date in descending order (newest first).</li>
                        <li>Date filters use inclusive ranges (from and to dates are included).</li>
                        <li>The response includes pagination metadata for easy navigation.</li>
                        <li>Empty results will return an empty array with pagination information.</li>
                    </ul>
                </div>
            </div>

            <!-- Departments API Section -->
            <div class="card mb-4" style="border-top: 3px solid #28a745;">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-building me-2"></i>Get Departments API
                    </h5>
                </div>
            </div>

            <!-- Departments Endpoint Overview -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Endpoint Overview</h5>
                </div>
                <div class="card-body">
                    <div class="api-endpoint">
                        <span class="method-badge method-get">GET</span>
                        <code>{{ url('/api/departments') }}</code>
                    </div>
                    <p class="mt-3">
                        This endpoint allows external portals to retrieve a list of all available departments from the system. 
                        This is useful for populating department dropdowns in forms or for filtering requisitions by department.
                        The departments are returned in alphabetical order by name.
                    </p>
                </div>
            </div>

            <!-- Departments Request Example -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Request Examples</h5>
                    <button class="btn btn-sm btn-label-secondary copy-btn" onclick="copyToClipboard('dept-request-example')">
                        <i class="ti ti-copy me-1"></i> Copy
                    </button>
                </div>
                <div class="card-body">
                    <h6 class="mb-2">Get All Departments:</h6>
                    <div class="code-block" id="dept-request-example">
curl -X GET "{{ url('/api/departments') }}" \
  -H "Accept: application/json"
                    </div>

                    <h6 class="mb-2 mt-4">JavaScript (Fetch) Example:</h6>
                    <div class="code-block" id="dept-request-js-example">
// Get all departments
fetch('{{ url('/api/departments') }}', {
  method: 'GET',
  headers: {
    'Accept': 'application/json'
  }
})
.then(response => response.json())
.then(data => {
  console.log(data);
  // Use data.data to populate dropdown
  data.data.forEach(dept => {
    console.log(`ID: ${dept.id}, Name: ${dept.name}`);
  });
})
.catch(error => console.error('Error:', error));
                    </div>

                    <h6 class="mb-2 mt-4">PHP Example:</h6>
                    <div class="code-block" id="dept-request-php-example">
$url = '{{ url('/api/departments') }}';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json'
]);
$response = curl_exec($ch);
curl_close($ch);
$result = json_decode($response, true);

if ($result['success']) {
    foreach ($result['data'] as $dept) {
        echo "ID: {$dept['id']}, Name: {$dept['name']}\n";
    }
}
                    </div>
                </div>
            </div>

            <!-- Departments Response Example -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Success Response</h5>
                    <button class="btn btn-sm btn-label-secondary copy-btn" onclick="copyToClipboard('dept-response-example')">
                        <i class="ti ti-copy me-1"></i> Copy
                    </button>
                </div>
                <div class="card-body">
                    <p><strong>HTTP Status Code:</strong> <code>200 OK</code></p>
                    <div class="code-block response-example" id="dept-response-example">
{
  "success": true,
  "message": "Departments retrieved successfully.",
  "data": [
    {
      "id": 1,
      "name": "Academic Department",
      "created_at": "2024-01-01 10:00:00",
      "updated_at": "2024-01-01 10:00:00"
    },
    {
      "id": 2,
      "name": "Administration",
      "created_at": "2024-01-01 10:00:00",
      "updated_at": "2024-01-01 10:00:00"
    },
    {
      "id": 3,
      "name": "IT Department",
      "created_at": "2024-01-01 10:00:00",
      "updated_at": "2024-01-01 10:00:00"
    }
  ],
  "total": 3
}
                    </div>
                </div>
            </div>

            <!-- Departments Response Fields -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Response Fields</h5>
                </div>
                <div class="card-body">
                    <table class="param-table">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Type</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>success</code></td>
                                <td>boolean</td>
                                <td>Indicates if the request was successful</td>
                            </tr>
                            <tr>
                                <td><code>message</code></td>
                                <td>string</td>
                                <td>Response message</td>
                            </tr>
                            <tr>
                                <td><code>data</code></td>
                                <td>array</td>
                                <td>Array of department objects</td>
                            </tr>
                            <tr>
                                <td><code>data[].id</code></td>
                                <td>integer</td>
                                <td>Unique department ID (use this for <code>department_id</code> in requisition submissions)</td>
                            </tr>
                            <tr>
                                <td><code>data[].name</code></td>
                                <td>string</td>
                                <td>Department name</td>
                            </tr>
                            <tr>
                                <td><code>data[].created_at</code></td>
                                <td>string (datetime)</td>
                                <td>Date and time when the department was created</td>
                            </tr>
                            <tr>
                                <td><code>data[].updated_at</code></td>
                                <td>string (datetime)</td>
                                <td>Date and time when the department was last updated</td>
                            </tr>
                            <tr>
                                <td><code>total</code></td>
                                <td>integer</td>
                                <td>Total number of departments returned</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Departments Notes -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Important Notes</h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li>The API endpoint does not require authentication.</li>
                        <li>No query parameters are required or accepted.</li>
                        <li>Departments are returned in alphabetical order by name.</li>
                        <li>This endpoint is useful for populating department dropdowns in forms.</li>
                        <li>Use the <code>id</code> field from the response as the <code>department_id</code> value when submitting bus requisitions.</li>
                        <li>The response includes all active departments in the system.</li>
                        <li>Empty results will return an empty array with <code>total: 0</code>.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.textContent || element.innerText;
    
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="ti ti-check me-1"></i> Copied!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-label-secondary');
        
        setTimeout(function() {
            btn.innerHTML = originalHtml;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-label-secondary');
        }, 2000);
    }).catch(function(err) {
        console.error('Failed to copy:', err);
        alert('Failed to copy to clipboard');
    });
}
</script>
@endsection

