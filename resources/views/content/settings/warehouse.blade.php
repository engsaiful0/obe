@extends('layouts/layoutMaster')

@section('title', 'Warehouse Settings')

@section('page-script')
    <script>
        window.warehouseUrls = AppUtils.buildApiUrls('app/settings/warehouse');
    </script>
    <script src="{{ asset('assets/js/warehouse-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Warehouse Name</th>
                        <th>Warehouse Number</th>
                        <th>Address</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" id="add-new-record">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="exampleModalLabel">New Warehouse</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="warehouse_name">Warehouse Name</label>
                    <div class="input-group input-group-merge">
                        <span id="warehouse_name2" class="input-group-text"><i class="ti ti-building-warehouse"></i></span>
                        <input type="text" id="warehouse_name" class="form-control dt-warehouse-name" name="warehouse_name" placeholder="Enter Warehouse Name" aria-label="Enter Warehouse Name" aria-describedby="warehouse_name2" />
                    </div>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="warehouse_number">Warehouse Number</label>
                    <div class="input-group input-group-merge">
                        <span id="warehouse_number2" class="input-group-text"><i class="ti ti-hash"></i></span>
                        <input type="text" id="warehouse_number" class="form-control dt-warehouse-number" name="warehouse_number" placeholder="Enter Warehouse Number" aria-label="Enter Warehouse Number" aria-describedby="warehouse_number2" />
                    </div>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="address">Address</label>
                    <div class="input-group input-group-merge">
                        <span id="address2" class="input-group-text"><i class="ti ti-map-pin"></i></span>
                        <textarea id="address" class="form-control dt-address" name="address" placeholder="Enter Address" aria-label="Enter Address" aria-describedby="address2" rows="2"></textarea>
                    </div>
                </div>
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary data-submit me-sm-4 me-1">Submit</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection


