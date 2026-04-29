@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Rules - Apps')

@section('page-script')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('assets/js/app-access-rules.js') }}"></script>
      <script src="{{ asset('assets/js/modal-add-role.js') }}"></script>
@endsection

@section('content')
<h4 class="mb-1">Rules List</h4>

<p class="mb-6">A rule provided access to predefined menus and features so that depending on <br> assigned rule an administrator can have access to what user needs.</p>

<div class="row g-6">
    <div class="col-12">
        <!-- Role Table -->
        <div class="card">
            <div class="card-datatable table-responsive">
                <table class="datatables-rules table border-top" data-edit-url="{{ route('app-access-rules.edit', ['rule' => ':id']) }}"
                data-update-url="{{ route('app-access-rules.update', ['rule' => ':id']) }}"
                data-delete-url="{{ route('app-access-rules.destroy', ['rule' => ':id']) }}">
                    <thead>
                        <tr>
                            <th>Rule Name</th>
                            <th>Permissions</th>
                            <th class="d-none">Id</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rules as $rule)
                            <tr data-id="{{ $rule->id }}">
                                <td>{{ $rule->name }}</td>
                                <td>
                                    @foreach ($rule->permissions as $permission)
                                        <span class="badge bg-label-primary">{{ $permission->name }}</span>
                                    @endforeach
                                </td>
                                <td class="d-none">{{ $rule->id }}</td>
                                <td>
                                    {{-- Actions are rendered by datatables --}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <!--/ Role Table -->
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <button data-bs-target="#addRuleModal" data-bs-toggle="modal" class="btn btn-primary mb-4 text-nowrap add-new-rule">Add New Rule</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Rule Modal -->
<div class="modal fade" id="addRuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-simple modal-dialog-centered modal-add-new-rule">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-6">
                    <h4 class="role-title mb-2">Add New Rule</h4>
                    <p>Set rule permissions</p>
                </div>
                <!-- Add role form -->
                <form id="addRuleForm" class="row g-6" method="POST" action="{{ route('app-access-rules.store') }}">
                    @csrf
                    <div class="col-12">
                        <label class="form-label" for="modalRuleName">Rule Name</label>
                        <input type="text" id="modalRuleName" name="name" class="form-control" placeholder="Enter a rule name" tabindex="-1" />
                    </div>
                    <div class="col-12">
                        <h5 class="mb-6">Role Permissions</h5>
                        <!-- Permission table -->
                        <div class="table-responsive">
                            <table class="table table-flush-spacing">
                                <tbody>
                                    <tr>
                                        <td class="text-nowrap fw-medium text-heading">Administrator Access <i class="ti ti-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="Allows a full access to the system"></i></td>
                                        <td>
                                            <div class="d-flex justify-content-end">
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input" type="checkbox" id="addSelectAll" />
                                                    <label class="form-check-label" for="addSelectAll">
                                                        Select All
                                                    </label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @foreach ($permissions as $permission)
                                        <tr>
                                            <td class="text-nowrap fw-medium text-heading">{{ $permission->name }}</td>
                                            <td>
                                                <div class="d-flex justify-content-end">
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" id="permission{{ $permission->id }}" value="{{ $permission->id }}" />
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Permission table -->
                    </div>
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary me-3">Submit</button>
                        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    </div>
                </form>
                <!--/ Add role form -->
            </div>
        </div>
    </div>
</div>
<!--/ Add Role Modal -->

<!-- Edit Rule Modal -->
<div class="modal fade" id="editRuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-simple modal-dialog-centered modal-add-new-rule">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-6">
                    <h4 class="role-title mb-2">Edit Rule</h4>
                    <p>Set rule permissions</p>
                </div>
                <!-- Edit role form -->
                <form id="editRuleForm" class="row g-6" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editRuleId" name="id">
                    <div class="col-12">
                        <label class="form-label" for="modalRuleName">Rule Name</label>
                        <input type="text" id="modalRuleName" name="name" class="form-control" placeholder="Enter a rule name" tabindex="-1" />
                    </div>
                    <div class="col-12">
                        <h5 class="mb-6">Role Permissions</h5>
                        <!-- Permission table -->
                        <div class="table-responsive">
                            <table class="table table-flush-spacing">
                                <tbody>
                                    <tr>
                                        <td class="text-nowrap fw-medium text-heading">Administrator Access <i class="ti ti-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="Allows a full access to the system"></i></td>
                                        <td>
                                            <div class="d-flex justify-content-end">
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input" type="checkbox" id="editSelectAll" />
                                                    <label class="form-check-label" for="editSelectAll">
                                                        Select All
                                                    </label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @foreach ($permissions as $permission)
                                        <tr>
                                            <td class="text-nowrap fw-medium text-heading">{{ $permission->name }}</td>
                                            <td>
                                                <div class="d-flex justify-content-end">
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" id="editPermission{{ $permission->id }}" value="{{ $permission->id }}" />
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Permission table -->
                    </div>
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary me-3">Submit</button>
                        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    </div>
                </form>
                <!--/ Edit role form -->
            </div>
        </div>
    </div>
</div>
<!--/ Edit Role Modal -->
@endsection
