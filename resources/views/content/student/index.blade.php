@extends('layouts/layoutMaster')

@section('title', 'Employee List')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">Employee List</h5>
        <a href="{{ route('employees.add-employee') }}" class="btn btn-primary">Add Employee</a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ti ti-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if($employees->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Photo</th>
                            <th>Employee Name</th>
                            <th>Employee ID</th>
                            <th>Designation</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            @if(auth()->user()->hasPermissionTo('employee-view') || auth()->user()->hasPermissionTo('employee-edit') || auth()->user()->hasPermissionTo('employee-delete'))
                            <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $index => $employee)
                            <tr>
                                <td>{{ $employees->firstItem() + $index }}</td>
                                <td>
                                    @if($employee->picture)
                                        <img src="{{ asset('storage/app/public/' . $employee->picture) }}" 
                                             alt="{{ $employee->employee_name }}" 
                                             class="rounded-circle" 
                                             width="40" 
                                             height="40"
                                             style="object-fit: cover;">
                                    @else
                                        <div class="avatar avatar-sm bg-secondary rounded-circle d-flex align-items-center justify-content-center">
                                            <span class="text-white">{{ substr($employee->employee_name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $employee->employee_name }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $employee->employee_unique_id }}</span>
                                </td>
                                <td>{{ $employee->designation->designation_name ?? 'N/A' }}</td>
                                <td>{{ $employee->email }}</td>
                                <td>{{ $employee->mobile }}</td>
                                @if(auth()->user()->hasPermissionTo('employee-view') || auth()->user()->hasPermissionTo('employee-edit') || auth()->user()->hasPermissionTo('employee-delete'))
                                <td>
                                    <div class="btn-group" role="group">
                                        @if(auth()->user()->hasPermissionTo('employee-view'))
                                        <a href="{{ route('employees.show', $employee->id) }}" 
                                           class="btn btn-sm btn-outline-info">
                                            <i class="ti ti-eye"></i> View
                                        </a>
                                        @endif
                                        @if(auth()->user()->hasPermissionTo('employee-edit'))
                                        <a href="{{ route('employees.edit', $employee->id) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-edit"></i> Edit
                                        </a>
                                        @endif
                                        @if(auth()->user()->hasPermissionTo('employee-delete'))
                                        <form action="{{ route('employees.destroy', $employee->id) }}" 
                                              method="POST" 
                                              style="display: inline-block;"
                                              onsubmit="return confirm('Are you sure you want to delete this employee?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="ti ti-trash"></i> Delete
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <p class="text-muted mb-0">
                        Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} 
                        of {{ $employees->total() }} results
                    </p>
                </div>
                <div>
                    {{ $employees->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="ti ti-users" style="font-size: 4rem; color: #ccc;"></i>
                </div>
                <h5 class="text-muted">No employees found</h5>
                <p class="text-muted">Start by adding your first employee.</p>
                <a href="{{ route('employees.add-employee') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Add Employee
                </a>
            </div>
        @endif
    </div>
</div>
@endsection