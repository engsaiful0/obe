@extends('layouts/layoutMaster')

@section('title', 'Color Settings')

<!-- Page Scripts -->
@section('page-script')
    <script>
        window.colorUrls = AppUtils.buildApiUrls('app/settings/color');
        console.log('Color URLs:', window.colorUrls);
    </script>
    <script src="{{ asset('assets/js/color-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <!-- DataTable with Buttons -->
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Color Preview</th>
                        <th>Color Name</th>
                        <th>Color Code</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!-- Modal to add new record -->
    <div class="offcanvas offcanvas-end" id="add-new-record">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="exampleModalLabel">New Color</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="color_name">Color Name <span class="text-danger">*</span></label>
                    <div class="input-group input-group-merge">
                        <span id="color_name2" class="input-group-text"><i class="ti ti-palette"></i></span>
                        <input type="text" id="color_name" class="form-control dt-full-name" name="color_name"
                            placeholder="Enter Color Name" aria-label="Enter Color Name"
                            aria-describedby="color_name2" required />
                    </div>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="color_code">Color Code <span class="text-danger">*</span></label>
                    <div class="input-group input-group-merge">
                        <span id="color_code2" class="input-group-text"><i class="ti ti-color-swatch"></i></span>
                        <input type="text" id="color_code" class="form-control" name="color_code"
                            placeholder="#FF5733" aria-label="Enter Color Code"
                            aria-describedby="color_code2" required />
                        <input type="color" id="color_picker" class="form-control form-control-color" 
                            style="width: 50px;" title="Color Picker" />
                    </div>
                    <small class="form-text text-muted">Enter hex color code or use the color picker</small>
                </div>
                <div class="col-sm-12">
                    <label class="form-label">Color Preview</label>
                    <div class="d-flex align-items-center">
                        <div id="color_preview" style="width: 50px; height: 50px; border: 1px solid #ccc; border-radius: 5px; margin-right: 10px; cursor: pointer;" title="Click to open color picker"></div>
                        <div>
                            <div class="fw-bold" id="preview_text">Select a color</div>
                            <small class="text-muted" id="preview_code">#000000</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="description">Description</label>
                    <div class="input-group input-group-merge">
                        <span id="description2" class="input-group-text"><i class="ti ti-file-text"></i></span>
                        <textarea id="description" class="form-control" name="description" rows="3"
                            placeholder="Enter color description (optional)" aria-label="Enter color description"
                            aria-describedby="description2"></textarea>
                    </div>
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