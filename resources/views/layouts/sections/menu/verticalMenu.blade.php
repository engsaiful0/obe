@php
use Illuminate\Support\Facades\Route;
$configData = Helper::appClasses();
$currentRouteName = Route::currentRouteName();

// Helper function to check if menu item is active
if (!function_exists('isMenuActive')) {
function isMenuActive($slug, $currentRoute) {
if ($currentRoute === $slug) {
return 'active';
}
if (is_array($slug)) {
foreach($slug as $s) {
if (str_contains($currentRoute, $s) && strpos($currentRoute, $s) === 0) {
return 'active open';
}
}
} else {
if (str_contains($currentRoute, $slug) && strpos($currentRoute, $slug) === 0) {
return 'active open';
}
}
return '';
}
}
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

    <!-- ! Hide app brand if navbar-full -->
    @if(!isset($navbarFull))
    <div class="app-brand demo">
        <a href="{{route('dashboard-analytics')}}" class="app-brand-link">
            <span class="app-brand-logo demo">
                @if(isset($appSettings) && $appSettings && $appSettings->logo)
                <img style="height: 32px; width: 32px;" src="{{ asset('assets/img/branding/'.$appSettings->logo) }}" alt="Logo">
                @else
                @include('_partials.macros',["height"=>20])
                @endif
            </span>
            <span class="app-brand-text demo menu-text fw-bold">{{config('variables.templateName')}}</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ti menu-toggle-icon d-none d-xl-block align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-md align-middle"></i>
        </a>
    </div>
    @endif

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboards -->
        <li class="menu-item {{ $currentRouteName === 'dashboard' ? 'active' : '' }}">
            <a href="{{ url('/layouts/vertical') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-smart-home"></i>
                <div>{{ __('Dashboards') }}</div>
            </a>
        </li>

    
        <!-- Teacher -->
        @permission('add-teacher')
        <li class="menu-item {{ isMenuActive('teachers', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-users"></i>
                <div>{{ __('Teacher') }}</div>
            </a>
            <ul class="menu-sub">
                @permission('add-teacher')
                <li class="menu-item {{ $currentRouteName === 'teachers.create' ? 'active' : '' }}">
                    <a href="{{ route('teachers.create') }}" class="menu-link">
                        <div>{{ __('Add Teacher') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('view-teacher')
                <li class="menu-item {{ $currentRouteName === 'teachers.index' ? 'active' : '' }}">
                    <a href="{{ route('teachers.index') }}" class="menu-link">
                        <div>{{ __('View Teacher') }}</div>
                    </a>
                </li>
                @endpermission
            </ul>
        </li>
        @endpermission

        @permission('student-add')
        <!-- Student -->
        <li class="menu-item {{ isMenuActive('student', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-user-plus"></i>
                <div>{{ __('Student') }}</div>
            </a>
            <ul class="menu-sub">
                @permission('student-add')
                <li class="menu-item {{ $currentRouteName === 'student.add-student' ? 'active' : '' }}">
                    <a href="{{ url('/app/student/add-student') }}" class="menu-link">
                        <div>{{ __('Add Student') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('student-view')
                <li class="menu-item {{ $currentRouteName === 'student.view-student' ? 'active' : '' }}">
                    <a href="{{ url('/app/student/view-student') }}" class="menu-link">
                        <div>{{ __('View Student') }}</div>
                    </a>
                </li>
                @endpermission
            </ul>
        </li>
        @endpermission
        
       <!-- Teacher -->
        @permission('add-course-assignment')
        <li class="menu-item {{ isMenuActive('course-assignment', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-users"></i>
                <div>{{ __('Course Assignment') }}</div>
            </a>
            <ul class="menu-sub">
                @permission('add-course-assignment')
                <li class="menu-item {{ $currentRouteName === 'course-assignment.create' ? 'active' : '' }}">
                    <a href="{{ route('course-assignment.create') }}" class="menu-link">
                        <div>{{ __('Add Course Assignment') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('view-course-assignment')
                <li class="menu-item {{ $currentRouteName === 'course-assignment.index' ? 'active' : '' }}">
                    <a href="{{ route('course-assignment.index') }}" class="menu-link">
                        <div>{{ __('View Course Assignment') }}</div>
                    </a>
                </li>
                @endpermission
            </ul>
        </li>
        @endpermission

        <!-- Report -->
        <li class="menu-item {{ isMenuActive('app-report', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-report"></i>
                <div>{{ __('Report') }}</div>
            </a>
            <ul class="menu-sub">
              

                
               
                <li class="menu-item {{ $currentRouteName === 'student-in-report' ? 'active' : '' }}">
                    <a href="{{ url('app/student-in-report') }}" class="menu-link">
                        <div>{{ __('Student IN Report') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'student-out-report' ? 'active' : '' }}">
                    <a href="{{ url('app/student-out-report') }}" class="menu-link">
                        <div>{{ __('Student OUT Report') }}</div>
                    </a>
                </li>
                
            </ul>
        </li>

        <!-- Settings -->
        @permission('obe-settings-view')
        <li class="menu-item {{ isMenuActive('basic-settings', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-settings"></i>
                <div>{{ __('OBE Settings') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('visions.*') ? 'active' : '' }}">
                    <a href="{{ route('visions.index') }}" class="menu-link">
                        <div>{{ __('Visions') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('missions.*') ? 'active' : '' }}">
                    <a href="{{ route('missions.index') }}" class="menu-link">
                        <div>{{ __('Missions') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('peos.*') ? 'active' : '' }}">
                    <a href="{{ route('peos.index') }}" class="menu-link">
                        <div>{{ __('PEOs') }}</div>
                    </a>
                </li>
            </ul>
        </li>
        @endpermission
        <!-- OBE Settings -->

        <!-- Settings -->
        @permission('settings-view')
        <li class="menu-item {{ isMenuActive('basic-settings', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-settings"></i>
                <div>{{ __('Settings') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ $currentRouteName === 'app-setting' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/app-settings') }}" class="menu-link">
                        <div>{{ __('App Setting') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'faculty' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/faculty') }}" class="menu-link">
                        <div>{{ __('Faculty') }}</div>
                    </a>
                </li>
              
                
                <li class="menu-item {{ $currentRouteName === 'program' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/program') }}" class="menu-link">
                        <div>{{ __('Program') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'academic-session' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/academic-session') }}" class="menu-link">
                        <div>{{ __('Academic Session') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'semester' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/semester') }}" class="menu-link">
                        <div>{{ __('Semester') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'batch' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/batch') }}" class="menu-link">
                        <div>{{ __('Batch') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'course' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/course') }}" class="menu-link">
                        <div>{{ __('Course') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'section' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/section') }}" class="menu-link">
                        <div>{{ __('Section') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'blood-group' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/blood-group') }}" class="menu-link">
                        <div>{{ __('Blood Group') }}</div>
                    </a>
                </li>
               
                <li class="menu-item {{ $currentRouteName === 'cache-clear' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/cache-clear') }}" class="menu-link">
                        <div>{{ __('Cache Clear') }}</div>
                    </a>
                </li>
             
                <li class="menu-item {{ $currentRouteName === 'designation' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/designation') }}" class="menu-link">
                        <div>{{ __('Designation') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'app-settings-department' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/department') }}" class="menu-link">
                        <div>{{ __('Department') }}</div>
                    </a>
                </li>
              
             
                <li class="menu-item {{ $currentRouteName === 'educational-qualification' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/educational-qualification') }}" class="menu-link">
                        <div>{{ __('Educational Qualification') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'expense-head' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/expense-head') }}" class="menu-link">
                        <div>{{ __('Expense Head') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'experience-year' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/experience-year') }}" class="menu-link">
                        <div>{{ __('Experience Year') }}</div>
                    </a>
                </li>
              
                <li class="menu-item {{ $currentRouteName === 'gender' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/gender') }}" class="menu-link">
                        <div>{{ __('Gender') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'income-head' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/income-head') }}" class="menu-link">
                        <div>{{ __('Income Head') }}</div>
                    </a>
                </li>
              
               
              
                <li class="menu-item {{ $currentRouteName === 'marital-status' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/marital-status') }}" class="menu-link">
                        <div>{{ __('Marital Status') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'nationality' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/nationality') }}" class="menu-link">
                        <div>{{ __('Nationality') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'payment-method' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/payment-method') }}" class="menu-link">
                        <div>{{ __('Payment Method') }}</div>
                    </a>
                </li>
              
                <li class="menu-item {{ $currentRouteName === 'religion' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/religion') }}" class="menu-link">
                        <div>{{ __('Religion') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'app-settings-permission' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/permission') }}" class="menu-link">
                        <div>{{ __('Permissions') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'app-access-rules' ? 'active' : '' }}">
                    <a href="{{ url('rules') }}" class="menu-link">
                        <div>{{ __('Rules & Permissions') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'status-related-to' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/status-related-to') }}" class="menu-link">
                        <div>{{ __('Related To') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'status' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/status') }}" class="menu-link">
                        <div>{{ __('Status') }}</div>
                    </a>
                </li>
            
            
                <li class="menu-item {{ $currentRouteName === 'users' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/users') }}" class="menu-link">
                        <div>{{ __('Users') }}</div>
                    </a>
                </li>
             
                <li class="menu-item {{ $currentRouteName === 'year' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/year') }}" class="menu-link">
                        <div>{{ __('Year') }}</div>
                    </a>
                </li>
            </ul>
        </li>
        @endpermission
        <!-- Trip -->

    </ul>

</aside>
