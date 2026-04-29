@extends('layouts/layoutMaster')

@section('title', 'Item Settings')

<!-- Page Scripts -->
@section('page-script')
    <script>
        window.itemUrls = AppUtils.buildApiUrls('app/settings/item');
        console.log('Item URLs:', window.itemUrls);
    </script>
    <script src="{{ asset('assets/js/item-datatables.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
    <!-- DataTable with Buttons -->
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Item Name</th>
                        <th>Opening Stock</th>
                        <th>Related To</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!-- Modal to add new record -->
    <div class="offcanvas offcanvas-end" id="add-new-record">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="exampleModalLabel">New Item</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
                <div class="col-sm-12">
                    <label class="form-label" for="item_name">Item Name</label>
                    <div class="input-group input-group-merge">
                        <span id="item_name2" class="input-group-text"><i class="ti ti-package"></i></span>
                        <input type="text" id="item_name" class="form-control dt-full-name" name="item_name"
                            placeholder="Enter Item Name" aria-label="Enter Item Name"
                            aria-describedby="item_name2" />
                    </div>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="opening_stock">Opening Stock</label>
                    <div class="input-group input-group-merge">
                        <span id="opening_stock2" class="input-group-text"><i class="ti ti-hash"></i></span>
                        <input type="number" id="opening_stock" class="form-control dt-opening-stock" name="opening_stock"
                            placeholder="Enter Opening Stock" aria-label="Enter Opening Stock"
                            aria-describedby="opening_stock2" step="1" min="0" />
                    </div>
                </div>
                <div class="col-sm-12">
                    <label class="form-label" for="related_to">Related To</label>
                    <select id="related_to" class="form-select" name="related_to">
                        <option value="">Select Related To</option>
                        <option value="Bus">Bus</option>
                        <option value="Fuel and Lubricant">Fuel and Lubricant</option>
                        <option value="Other">Other</option>
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
