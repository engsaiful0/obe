@extends('layouts/layoutMaster')

@section('title', 'API Documentation - POST Requisition')

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
    .method-post {
        background: #28a745;
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
        <h4 class="mb-0">API Documentation - Submit Requisition (POST)</h4>
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
                        <span class="method-badge method-post">POST</span>
                        <code>{{ url('/api/bus-requisitions') }}</code>
                    </div>
                    <p class="mt-3">
                        This endpoint allows external portals to submit bus requisitions to the system. 
                        The requisition will be created with a default status of "pending" and will be 
                        available in the system for review and approval.
                    </p>
                </div>
            </div>

            <!-- Request Parameters -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Request Parameters</h5>
                </div>
                <div class="card-body">
                    <p>All parameters should be sent in the request body as JSON or form data.</p>
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
                                <td><code>date</code></td>
                                <td>string (date)</td>
                                <td><span class="required">Yes</span></td>
                                <td>Date of the requisition (format: YYYY-MM-DD)</td>
                            </tr>
                            <tr>
                                <td><code>purpose</code></td>
                                <td>string</td>
                                <td><span class="required">Yes</span></td>
                                <td>Purpose of the bus requisition (max: 1000 characters)</td>
                            </tr>
                            <tr>
                                <td><code>required_bus_date</code></td>
                                <td>string (date)</td>
                                <td><span class="required">Yes</span></td>
                                <td>Date when the bus is required (format: YYYY-MM-DD)</td>
                            </tr>
                            <tr>
                                <td><code>required_time</code></td>
                                <td>string (time)</td>
                                <td><span class="required">Yes</span></td>
                                <td>Time when the bus is required (format: HH:MM or HH:MM:SS)</td>
                            </tr>
                            <tr>
                                <td><code>number_of_buses</code></td>
                                <td>integer</td>
                                <td><span class="required">Yes</span></td>
                                <td>Number of buses required (minimum: 1)</td>
                            </tr>
                            <tr>
                                <td><code>total_passengers</code></td>
                                <td>integer</td>
                                <td><span class="required">Yes</span></td>
                                <td>Total number of passengers (minimum: 1)</td>
                            </tr>
                            <tr>
                                <td><code>department_id</code></td>
                                <td>integer</td>
                                <td><span class="required">Yes</span></td>
                                <td>ID of the department (must exist in departments table)</td>
                            </tr>
                            <tr>
                                <td><code>requisition_sender_name</code></td>
                                <td>string</td>
                                <td><span class="required">Yes</span></td>
                                <td>Name of the person sending the requisition (max: 255 characters)</td>
                            </tr>
                            <tr>
                                <td><code>mobile_number</code></td>
                                <td>string</td>
                                <td><span class="required">Yes</span></td>
                                <td>Mobile number of the sender (max: 20 characters)</td>
                            </tr>
                            <tr>
                                <td><code>email_address</code></td>
                                <td>string (email)</td>
                                <td><span class="required">Yes</span></td>
                                <td>Email address of the sender (valid email format, max: 255 characters)</td>
                            </tr>
                            <tr>
                                <td><code>remarks</code></td>
                                <td>string</td>
                                <td><span class="optional">No</span></td>
                                <td>Additional remarks or notes (max: 1000 characters)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Request Example -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Request Example</h5>
                    <button class="btn btn-sm btn-label-secondary copy-btn" onclick="copyToClipboard('request-example')">
                        <i class="ti ti-copy me-1"></i> Copy
                    </button>
                </div>
                <div class="card-body">
                    <h6 class="mb-2">cURL Example:</h6>
                    <div class="code-block" id="request-example">
curl -X POST {{ url('/api/bus-requisitions') }} \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "date": "2024-01-15",
    "purpose": "Transportation for annual conference attendees",
    "required_bus_date": "2024-01-20",
    "required_time": "08:00",
    "number_of_buses": 3,
    "total_passengers": 120,
    "department_id": 1,
    "requisition_sender_name": "John Doe",
    "mobile_number": "+8801712345678",
    "email_address": "john.doe@example.com",
    "remarks": "Please ensure buses are air-conditioned"
  }'
                    </div>

                    <h6 class="mb-2 mt-4">JavaScript (Fetch) Example:</h6>
                    <div class="code-block" id="request-js-example">
fetch('{{ url('/api/bus-requisitions') }}', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    date: '2024-01-15',
    purpose: 'Transportation for annual conference attendees',
    required_bus_date: '2024-01-20',
    required_time: '08:00',
    number_of_buses: 3,
    total_passengers: 120,
    department_id: 1,
    requisition_sender_name: 'John Doe',
    mobile_number: '+8801712345678',
    email_address: 'john.doe@example.com',
    remarks: 'Please ensure buses are air-conditioned'
  })
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));
                    </div>

                    <h6 class="mb-2 mt-4">PHP Example:</h6>
                    <div class="code-block" id="request-php-example">
$data = [
    'date' => '2024-01-15',
    'purpose' => 'Transportation for annual conference attendees',
    'required_bus_date' => '2024-01-20',
    'required_time' => '08:00',
    'number_of_buses' => 3,
    'total_passengers' => 120,
    'department_id' => 1,
    'requisition_sender_name' => 'John Doe',
    'mobile_number' => '+8801712345678',
    'email_address' => 'john.doe@example.com',
    'remarks' => 'Please ensure buses are air-conditioned'
];

$ch = curl_init('{{ url('/api/bus-requisitions') }}');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
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
                    <p><strong>HTTP Status Code:</strong> <code>201 Created</code></p>
                    <div class="code-block response-example" id="response-example">
{
  "success": true,
  "message": "Bus requisition submitted successfully.",
  "data": {
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
    "created_at": "2024-01-15 10:30:00"
  }
}
                    </div>
                </div>
            </div>

            <!-- Error Response -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Error Response</h5>
                </div>
                <div class="card-body">
                    <p><strong>HTTP Status Code:</strong> <code>422 Unprocessable Entity</code> (Validation Error) or <code>500 Internal Server Error</code></p>
                    <div class="code-block">
{
  "message": "The given data was invalid.",
  "errors": {
    "date": ["The date field is required."],
    "department_id": ["The selected department id is invalid."]
  }
}
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Important Notes</h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li>The API endpoint does not require authentication for external portal submissions.</li>
                        <li>All requisitions submitted via API will have a default status of "pending".</li>
                        <li>The <code>user_id</code> field will be set to null for API submissions.</li>
                        <li>Date format must be <code>YYYY-MM-DD</code> (e.g., 2024-01-15).</li>
                        <li>Time format can be <code>HH:MM</code> or <code>HH:MM:SS</code> (e.g., 08:00 or 08:00:00).</li>
                        <li>Make sure the <code>department_id</code> exists in the departments table.</li>
                        <li>The API returns the created requisition data including the generated ID.</li>
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

