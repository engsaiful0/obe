@extends('layouts/layoutMaster')

@section('title', 'Teachers')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Teacher Management</h5>
        <a href="{{ route('teachers.create') }}" class="btn btn-primary btn-sm">Add Teacher</a>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Search name / employee ID / email"></div>
            <div class="col-md-3">
                <select name="department_id" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="Active" @selected(request('status')==='Active')>Active</option>
                    <option value="Inactive" @selected(request('status')==='Inactive')>Inactive</option>
                </select>
            </div>
            <div class="col-md-3"><button class="btn btn-outline-primary">Filter</button></div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Employee ID</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $teacher)
                        <tr>
                            <td>{{ $teacher->name }}</td>
                            <td>{{ $teacher->employee_id }}</td>
                            <td>{{ $teacher->department->name ?? 'N/A' }}</td>
                            <td>{{ $teacher->designation }}</td>
                            <td>{{ $teacher->email }}</td>
                            <td><span class="badge {{ $teacher->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">{{ $teacher->status }}</span></td>
                            <td class="d-flex gap-1">
                                <a class="btn btn-info btn-sm" href="{{ route('teachers.show', $teacher->id) }}">View</a>
                                <a class="btn btn-warning btn-sm" href="{{ route('teachers.edit', $teacher->id) }}">Edit</a>
                                <form method="POST" action="{{ route('teachers.destroy', $teacher->id) }}" onsubmit="return confirm('Delete this teacher?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $teachers->links() }}
    </div>
</div>
@endsection
