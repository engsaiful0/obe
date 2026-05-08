@extends('layouts/layoutMaster')

@section('title', __('My Courses'))

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('My Assigned Courses') }}</h5>
        </div>
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" id="my-course-search" class="form-control form-control-sm"
                        placeholder="{{ __('Search by course name or code...') }}" value="{{ request('search') }}">
                </div>
            </div>

            <div id="my-course-table-wrapper" class="position-relative border rounded">
                <div id="my-course-loading"
                    class="d-none position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center"
                    style="z-index: 5;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">{{ __('Loading') }}</span>
                    </div>
                </div>
                <div id="my-course-table-container">
                    @include('content.my-courses.partials.course-list-table', ['courses' => $courses])
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        window.__myCoursesRoutes = {
            list: @json(route('my-courses.course-list'))
        };
    </script>
    <script src="{{ asset('assets/js/my-courses.js') }}"></script>
@endsection
