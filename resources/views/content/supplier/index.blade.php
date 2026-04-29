@extends('layouts/layoutMaster')

@section('title', 'Supplier Management')

<!-- Vendor Styles -->
@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/form-validation/form-validation.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/toastr/toastr.css') }}">
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/bundle/popular.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/toastr/toastr.js') }}"></script>
@endsection

<!-- Page Scripts -->
@section('page-script')
    <script>
        window.supplierUrls = AppUtils.buildApiUrls('app/supplier');
        console.log('Supplier URLs:', window.supplierUrls);
    </script>
    <script src="{{ asset('assets/js/supplier-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <!-- DataTable with Buttons -->
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Supplier Name</th>
                        <th>Contact Person</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Joining Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    
    <!-- Modal to add new record -->
    <div class="offcanvas offcanvas-end" id="add-new-record">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="exampleModalLabel">New Supplier</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false" enctype="multipart/form-data">
                <div class="col-sm-12">
                    <label class="form-label" for="supplier_name">Supplier Name *</label>
                    <div class="input-group input-group-merge">
                        <span id="supplier_name2" class="input-group-text"><i class="ti ti-building"></i></span>
                        <input type="text" id="supplier_name" class="form-control dt-full-name" name="supplier_name"
                            placeholder="Enter Supplier Name" aria-label="Enter Supplier Name"
                            aria-describedby="supplier_name2" />
                    </div>
                </div>
                
                <div class="col-sm-12">
                    <label class="form-label" for="address">Address</label>
                    <textarea id="address" class="form-control" name="address" rows="3" placeholder="Enter Address"></textarea>
                </div>
                
                <div class="col-sm-6">
                    <label class="form-label" for="mobile">Mobile</label>
                    <div class="input-group input-group-merge">
                        <span id="mobile2" class="input-group-text"><i class="ti ti-phone"></i></span>
                        <input type="text" id="mobile" class="form-control" name="mobile"
                            placeholder="Enter Mobile Number" aria-label="Enter Mobile Number"
                            aria-describedby="mobile2" />
                    </div>
                </div>
                
                <div class="col-sm-6">
                    <label class="form-label" for="email">Email</label>
                    <div class="input-group input-group-merge">
                        <span id="email2" class="input-group-text"><i class="ti ti-mail"></i></span>
                        <input type="email" id="email" class="form-control" name="email"
                            placeholder="Enter Email" aria-label="Enter Email"
                            aria-describedby="email2" />
                    </div>
                </div>
                
                <div class="col-sm-12">
                    <label class="form-label" for="contact_person_name">Contact Person Name</label>
                    <div class="input-group input-group-merge">
                        <span id="contact_person_name2" class="input-group-text"><i class="ti ti-user"></i></span>
                        <input type="text" id="contact_person_name" class="form-control" name="contact_person_name"
                            placeholder="Enter Contact Person Name" aria-label="Enter Contact Person Name"
                            aria-describedby="contact_person_name2" />
                    </div>
                </div>
                
                <div class="col-sm-6">
                    <label class="form-label" for="contact_person_mobile">Contact Person Mobile</label>
                    <div class="input-group input-group-merge">
                        <span id="contact_person_mobile2" class="input-group-text"><i class="ti ti-phone"></i></span>
                        <input type="text" id="contact_person_mobile" class="form-control" name="contact_person_mobile"
                            placeholder="Enter Contact Person Mobile" aria-label="Enter Contact Person Mobile"
                            aria-describedby="contact_person_mobile2" />
                    </div>
                </div>
                
                <div class="col-sm-6">
                    <label class="form-label" for="contact_person_email">Contact Person Email</label>
                    <div class="input-group input-group-merge">
                        <span id="contact_person_email2" class="input-group-text"><i class="ti ti-mail"></i></span>
                        <input type="email" id="contact_person_email" class="form-control" name="contact_person_email"
                            placeholder="Enter Contact Person Email" aria-label="Enter Contact Person Email"
                            aria-describedby="contact_person_email2" />
                    </div>
                </div>
                
                <div class="col-sm-12">
                    <label class="form-label" for="working_experience">Working Experience</label>
                    <textarea id="working_experience" class="form-control" name="working_experience" rows="3" placeholder="Enter Working Experience"></textarea>
                </div>
                
                <div class="col-sm-6">
                    <label class="form-label" for="joining_date">Joining Date</label>
                    <div class="input-group input-group-merge">
                        <span id="joining_date2" class="input-group-text"><i class="ti ti-calendar"></i></span>
                        <input type="date" id="joining_date" class="form-control" name="joining_date"
                            aria-label="Enter Joining Date" aria-describedby="joining_date2" />
                    </div>
                </div>
                
                <div class="col-sm-6">
                    <label class="form-label" for="trade_license_number">Trade License Number</label>
                    <div class="input-group input-group-merge">
                        <span id="trade_license_number2" class="input-group-text"><i class="ti ti-file-text"></i></span>
                        <input type="text" id="trade_license_number" class="form-control" name="trade_license_number"
                            placeholder="Enter Trade License Number" aria-label="Enter Trade License Number"
                            aria-describedby="trade_license_number2" />
                    </div>
                </div>
                
                <div class="col-sm-12">
                    <label class="form-label" for="trade_license_picture">Trade License Picture</label>
                    <input type="file" id="trade_license_picture" class="form-control" name="trade_license_picture"
                        accept="image/*" />
                    <small class="text-muted">Upload image (JPEG, PNG, JPG, GIF) - Max 2MB</small>
                </div>
                
                <div class="col-sm-12">
                    <label class="form-label" for="status">Status *</label>
                    <select id="status" class="form-select" name="status">
                        <option value="">Select Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary data-submit me-sm-4 me-1">Submit</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <!--/ DataTable with Buttons -->
@endsection
