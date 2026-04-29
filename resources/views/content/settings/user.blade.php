@extends('layouts/layoutMaster')

@section('title', 'User Management - Crud App')

@section('page-script')
    <script>
        window.userUrls = AppUtils.buildApiUrls('app/settings/users');
        console.log('User URLs:', window.userUrls);
    </script>
    <script src="{{ asset('assets/js/user-management.js') }}?v={{ time() }}"></script>
@endsection
@section('content')

    <!-- Users List Table -->
    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Search Filter</h5>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table">
                <thead class="border-top">
                    <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th>Rule</th>
                        <th>Email</th>
                        <th>Picture</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="offcanvas offcanvas-end" id="add-new-record">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title" id="exampleModalLabel">New User</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body flex-grow-1">
                <form class="add-new-record pt-0" id="form-add-new-record" enctype="multipart/form-data">
                    <div class="col-sm-12">
                        <div class="mb-6">
                            <label class="form-label" for="add-user-fullname">Full Name</label>
                            <input type="text" class="form-control dt-full-name" id="add-user-fullname"
                                placeholder="John Doe" name="name" aria-label="John Doe" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="rule_id">Rule</label>
                            <select id="rule_id" name="rule_id" class="form-select rule-select">
                                <option value="" disabled selected>Select a rule</option> <!-- Optional fallback -->
                            </select>
                        </div>
                        <div class="mb-6">
                            <label class="form-label" for="email">Email</label>
                            <input type="text" id="email" class="form-control dt-email"
                                placeholder="john.doe@example.com" aria-label="john.doe@example.com" name="email" />
                        </div>
                        <div class="mb-6">
                            <label class="form-label" for="add-user-password">Password</label>
                            <input type="password" id="add-user-password" class="form-control " placeholder="••••••••"
                                aria-label="••••••••" name="password" />
                        </div>
                        <div class="mb-6">
                            <label class="form-label" for="profile_picture">Profile Picture</label>
                            <input type="file" class="form-control" id="profile_picture" name="profile_picture" />
                        </div>
                    </div>
                    <div class="col-sm-12 mt-3">
                        <button type="submit" class="btn btn-primary me-3 data-submit">Submit</button>
                        <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
