@extends('layouts/layoutMaster')

@section('title', 'Bus Route Settings')

<!-- Page Scripts -->
@section('page-script')
    <script>
        // Manual URL configuration for vehicle routes
        window.vehicleRouteUrls = {
            getData: AppUtils.buildUrl('app/settings/get-bus-route'),
            store: AppUtils.buildUrl('app/settings/bus-route'),
            update: AppUtils.buildUrl('app/settings/bus-route'),
            destroy: AppUtils.buildUrl('app/settings/bus-route')
        };
        console.log('Bus Route URLs:', window.busRouteUrls);
    </script>
    <script src="{{ asset('assets/js/bus-route-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <!-- Enhanced Spinner Styles -->
    <style>
        .spinner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .btn-loading {
            position: relative;
            pointer-events: none;
        }
        
        .btn-loading .spinner-border {
            display: inline-block !important;
        }
        
        .btn-loading .btn-text {
            opacity: 0.7;
        }
        
        .table-loading {
            opacity: 0.6;
            pointer-events: none;
        }
    </style>
    
    <!-- DataTable with Buttons -->
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Route Name</th>
                        <th>Start Stoppage</th>
                        <th>End Stoppage</th>
                        <th>Distance</th>
                        <th>Estimated Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!-- Modal to add new record -->
    <div class="offcanvas offcanvas-end" id="add-new-record">
        <div class="offcanvas-header border-bottom">
            <h6 id="modal-title" class="offcanvas-title">Add New Bus Route</h6>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0">
            <form class="add-new-record pt-0" id="form-add-new-record" onsubmit="return false">
                <div class="mb-3">
                    <label class="form-label" for="route_name">Route Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control dt-full-name" id="route_name" name="route_name" placeholder="Enter route name" />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter route description"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="start_stoppage_id">Start Stoppage <span class="text-danger">*</span></label>
                    <select class="form-select" id="start_stoppage_id" name="start_stoppage_id" required>
                        <option value="">Select Start Stoppage</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="end_stoppage_id">End Stoppage <span class="text-danger">*</span></label>
                    <select class="form-select" id="end_stoppage_id" name="end_stoppage_id" required>
                        <option value="">Select End Stoppage</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="distance">Distance (km)</label>
                    <input type="number" class="form-control" id="distance" name="distance" step="0.01" min="0" placeholder="Enter distance in km" />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="estimated_time">Estimated Time (minutes)</label>
                    <input type="number" class="form-control" id="estimated_time" name="estimated_time" min="0" placeholder="Enter estimated time in minutes" />
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked />
                        <label class="form-check-label" for="is_active">
                            Active Route
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">
                    <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                    <span class="btn-text">Save</span>
                </button>
                <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            </form>
        </div>
    </div>
@endsection
