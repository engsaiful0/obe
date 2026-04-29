@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Edit Rule - Apps')


@section('page-script')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('assets/js/app-access-rules.js') }}"></script>
    <script src="{{ asset('assets/js/modal-add-role.js') }}"></script>
@endsection
@section('content')
<h4 class="mb-1">Edit Rule</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form id="editRuleForm" class="row g-6" method="POST" action="{{ route('app-access-rules.update', $rule->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="col-12">
                        <label class="form-label" for="modalRuleName">Rule Name</label>
                        <input type="text" id="modalRuleName" name="name" class="form-control" value="{{ $rule->name }}" placeholder="Enter a rule name" tabindex="-1" />
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
                                                    <input class="form-check-input" type="checkbox" id="selectAll" />
                                                    <label class="form-check-label" for="selectAll">
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
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" @if($rule->permissions->contains($permission)) checked @endif />
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
                        <a href="{{ route('app-access-rules.index') }}" class="btn btn-label-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
