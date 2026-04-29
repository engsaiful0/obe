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

        <!--  Bus Helper -->
        <li class="menu-item {{ isMenuActive('app-bus-helpers', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-user-plus"></i>
                <div>{{ __('Bus Helper') }}</div>
            </a>
            <ul class="menu-sub">
                @permission('bus-helper-add')
                <li class="menu-item {{ $currentRouteName === 'app-bus-helpers-create' ? 'active' : '' }}">
                    <a href="{{ url('app/bus-helpers/add-bus-helper') }}" class="menu-link">
                        <div>{{ __('Add Helper') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('bus-helper-view')
                <li class="menu-item {{ $currentRouteName === 'app-bus-helpers-view' ? 'active' : '' }}">
                    <a href="{{ url('app/bus-helpers/view-bus-helper') }}" class="menu-link">
                        <div>{{ __('View Helpers') }}</div>
                    </a>
                </li>
                @endpermission
            </ul>
        </li>

        <!-- Bus Schedule -->
        <li class="menu-item {{ isMenuActive('bus-schedules', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-list-check"></i>
                <div>{{ __('Bus Schedule') }}</div>
            </a>
            <ul class="menu-sub">
                @permission('bus-schedule-add')
                <li class="menu-item {{ $currentRouteName === 'bus-schedules.create-schedule' || $currentRouteName === 'bus-schedules.schedule-index' ? 'active' : '' }}">
                    <a href="{{ route('bus-schedules.schedule-index') }}" class="menu-link">
                        <div>{{ __('Bus Schedule') }}</div>
                    </a>
                </li>

                @endpermission

            </ul>
        </li>

        <!-- Deployment Plan -->
        <li class="menu-item {{ isMenuActive('app-deployment-plans', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-list-check"></i>
                <div>{{ __('Deployment Plan') }}</div>
            </a>
            <ul class="menu-sub">
                @permission('daily-deployment-plan-add')
                <li class="menu-item {{ $currentRouteName === 'deployment-plans.create-daily-deployment-plan' ? 'active' : '' }}">
                    <a href="{{ url('/app/deployment-plans/create-daily-deployment-plan') }}" class="menu-link">
                        <div>{{ __('Add Daily Plan') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('daily-deployment-plan-view')
                <li class="menu-item {{ $currentRouteName === 'deployment-plans.view-daily-deployment-plan' ? 'active' : '' }}">
                    <a href="{{ url('/app/deployment-plans/view-daily-deployment-plan') }}" class="menu-link">
                        <div>{{ __('View Daily Plan') }}</div>
                    </a>
                </li>
                @endpermission

            </ul>
        </li>

        <li class="menu-item {{ isMenuActive('bus-trip', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-bus-off"></i>
                <div>{{ __('Trip') }}</div>
            </a>
            <ul class="menu-sub">
                @permission('bus-trip-add')
                <li class="menu-item {{ $currentRouteName === 'bus-trip.add-bus-trip' ? 'active' : '' }}">
                    <a href="{{ url('app/bus-trip/add-all-bus-trip') }}" class="menu-link">
                        <div>{{ __('Add All Bus Trip') }}</div>
                    </a>
                </li>
                <!-- <li class="menu-item {{ $currentRouteName === 'bus-trip.add-bus-trip' ? 'active' : '' }}">
          <a href="{{ url('app/bus-trip/add-bus-trip') }}" class="menu-link">
            <div>{{ __('Add Trip') }}</div>
          </a>
        </li> -->
                @endpermission
                @permission('bus-trip-view')
                <li class="menu-item {{ $currentRouteName === 'bus-trip.view-bus-trip' ? 'active' : '' }}">
                    <a href="{{ url('app/bus-trip/view-bus-trip') }}" class="menu-link">
                        <div>{{ __('View Trip') }}</div>
                    </a>
                </li>
                @endpermission
            </ul>
        </li>


        <li class="menu-item {{ isMenuActive('bus-requisitions', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-bus-off"></i>
                <div>{{ __('Bus Requisition') }}</div>
            </a>
            <ul class="menu-sub">
                @permission('add-bus-requisition')
                <li class="menu-item {{ $currentRouteName === 'app-bus-requisitions.create' ? 'active' : '' }}">
                    <a href="{{ route('app-bus-requisitions.create') }}" class="menu-link">
                        <div>{{ __('Add Requisition') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('view-bus-requisition')
                <li class="menu-item {{ $currentRouteName === 'app-bus-requisitions' ? 'active' : '' }}">
                    <a href="{{ route('app-bus-requisitions') }}" class="menu-link">
                        <div>{{ __('View Requisitions') }}</div>
                    </a>
                </li>
                @endpermission
                <li class="menu-item {{ $currentRouteName === 'app-bus-requisitions.api-doc-post' ? 'active' : '' }}">
                    <a href="{{ route('app-bus-requisitions.api-doc-post') }}" class="menu-link">
                        <div>{{ __('API Doc - POST') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'app-bus-requisitions.api-doc-get' ? 'active' : '' }}">
                    <a href="{{ route('app-bus-requisitions.api-doc-get') }}" class="menu-link">
                        <div>{{ __('API Doc - GET') }}</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Bus -->
        <li class="menu-item {{ isMenuActive('app-buses', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-car"></i>
                <div>{{ __('Bus') }}</div>
            </a>
            <ul class="menu-sub">

                @permission('bus-add')
                <li class="menu-item {{ $currentRouteName === 'app-buses-create' ? 'active' : '' }}">
                    <a href="{{ url('app/buses/add-bus') }}" class="menu-link">
                        <div>{{ __('Add Bus') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('bus-view')
                <li class="menu-item {{ $currentRouteName === 'app-buses-index' ? 'active' : '' }}">
                    <a href="{{ url('app/buses') }}" class="menu-link">
                        <div>{{ __('View Buses') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'app-buses-index' ? 'active' : '' }}">
                    <a href="{{ url('app/driver-helper-assignments') }}" class="menu-link">
                        <div>{{ __('Assign Driver & Helper To Bus') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'buses.assign-driver-helper-all' ? 'active' : '' }}">
                    <a href="{{ route('buses.assign-driver-helper-all') }}" class="menu-link">
                        <div>{{ __('Assign Driver and Helper All') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('bus-view')
                <li class="menu-item {{ $currentRouteName === 'app-buses-expired-documents' ? 'active' : '' }}">
                    <a href="{{ url('app/buses/expired-documents') }}" class="menu-link">
                        <div>{{ __('Expired Documents') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('bus-view')
                <li class="menu-item {{ $currentRouteName === 'app-buses-service-due' ? 'active' : '' }}">
                    <a href="{{ url('app/buses/service-due') }}" class="menu-link">
                        <div>{{ __('Service Due') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'fuels.index' || $currentRouteName === 'fuels.create' || $currentRouteName === 'fuels.edit' ? 'active' : '' }}">
                    <a href="{{ route('fuels.index') }}" class="menu-link">
                        <div>{{ __('Fuel') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'lubricants.index' || $currentRouteName === 'lubricants.create' || $currentRouteName === 'lubricants.edit' ? 'active' : '' }}">
                    <a href="{{ route('lubricants.index') }}" class="menu-link">
                        <div>{{ __('Lubricant') }}</div>
                    </a>
                </li>
                @endpermission

                @permission('punishment-view')
                <li class="menu-item {{ $currentRouteName === 'app-punishments-index' ? 'active' : '' }}">
                    <a href="{{ url('punishments') }}" class="menu-link">
                        <div>{{ __('Punishment') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('reward-view')
                <li class="menu-item {{ $currentRouteName === 'app-rewards-index' ? 'active' : '' }}">
                    <a href="{{ url('rewards') }}" class="menu-link">
                        <div>{{ __('Reward') }}</div>
                    </a>
                </li>
                @endpermission
            </ul>
        </li>



        <!-- Damage -->
        <li class="menu-item {{ isMenuActive('damage', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-logout"></i>
                <div>{{ __('Damage') }}</div>
            </a>
            <ul class="menu-sub">
                @permission('damage-add')
                <li class="menu-item {{ $currentRouteName === 'damage.add-damage' ? 'active' : '' }}">
                    <a href="{{ url('/app/damage/add-damage') }}" class="menu-link">
                        <div>{{ __('Add Damage') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('damage-view')
                <li class="menu-item {{ $currentRouteName === 'damage.view-damage' ? 'active' : '' }}">
                    <a href="{{ url('/app/damage/view-damage') }}" class="menu-link">
                        <div>{{ __('View Damage') }}</div>
                    </a>
                </li>
                @endpermission
            </ul>
        </li>

        <!-- Distance -->
        <!-- <li class="menu-item {{ isMenuActive('distance', $currentRouteName) }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-route"></i>
        <div>{{ __('Distance') }}</div>
      </a>
      <ul class="menu-sub">
        @permission('distance-view')
        <li class="menu-item {{ $currentRouteName === 'app-distances-index' ? 'active' : '' }}">
          <a href="{{ url('/app/distances') }}" class="menu-link">
            <div>{{ __('All Distances') }}</div>
          </a>
        </li>
        @endpermission
        @permission('distance-add')
        <li class="menu-item {{ $currentRouteName === 'app-distances-create' ? 'active' : '' }}">
          <a href="{{ url('/app/distances/create') }}" class="menu-link">
            <div>{{ __('Add Distance') }}</div>
          </a>
        </li>
        @endpermission
      </ul>
    </li> -->

        <!-- Driver -->
        <li class="menu-item {{ isMenuActive('app-drivers', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-user-check"></i>
                <div>{{ __('Driver') }}</div>
            </a>
            <ul class="menu-sub">
                @permission('driver-add')
                <li class="menu-item {{ $currentRouteName === 'app-drivers-create' ? 'active' : '' }}">
                    <a href="{{ url('app/drivers/add-driver') }}" class="menu-link">
                        <div>{{ __('Add Driver') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('driver-view')
                <li class="menu-item {{ $currentRouteName === 'app-drivers-index' ? 'active' : '' }}">
                    <a href="{{ url('app/drivers') }}" class="menu-link">
                        <div>{{ __('View Driver') }}</div>
                    </a>
                </li>
                @endpermission
            </ul>
        </li>

        <!-- Employee Attendance -->
        <li class="menu-item {{ isMenuActive('schedules', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-user-check"></i>
                <div>{{ __('Employee Attendance') }}</div>
            </a>
            <ul class="menu-sub">
                @permission('employee-attendance-add')
                <li class="menu-item {{ $currentRouteName === 'employee-attendance.add-employee-attendance' ? 'active' : '' }}">
                    <a href="{{ url('/app/employee-attendance/add-employee-attendance') }}" class="menu-link">
                        <div>{{ __('Add') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('employee-attendance-add')
                <li class="menu-item {{ $currentRouteName === 'employee-attendance.add-all-attendance' ? 'active' : '' }}">
                    <a href="{{ url('/app/employee-attendance/add-all-attendance') }}" class="menu-link">
                        <div>{{ __('Add All Attendance') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('employee-attendance-view')
                <li class="menu-item {{ $currentRouteName === 'employee-attendance.view-employee-attendance' ? 'active' : '' }}">
                    <a href="{{ url('/app/employee-attendance/view-employee-attendance') }}" class="menu-link">
                        <div>{{ __('View') }}</div>
                    </a>
                </li>
                @endpermission
            </ul>
        </li>

        <!-- Employee -->
        <li class="menu-item {{ isMenuActive('app-employees', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-users"></i>
                <div>{{ __('Employee') }}</div>
            </a>
            <ul class="menu-sub">
                @permission('employee-add')
                <li class="menu-item {{ $currentRouteName === 'app-add-employee' ? 'active' : '' }}">
                    <a href="{{ url('app/employees/add-employee') }}" class="menu-link">
                        <div>{{ __('Add Employee') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('employee-view')
                <li class="menu-item {{ $currentRouteName === 'app-view-employee' ? 'active' : '' }}">
                    <a href="{{ url('app/employees/view-employee') }}" class="menu-link">
                        <div>{{ __('View Employee') }}</div>
                    </a>
                </li>
                @endpermission
            </ul>
        </li>

        <!-- Expense -->
        <li class="menu-item {{ isMenuActive('app-expense', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-file-dollar"></i>
                <div>{{ __('Expense') }}</div>
            </a>
            <ul class="menu-sub">
                @permission('expense-view')
                <li class="menu-item {{ $currentRouteName === 'app-expenses' ? 'active' : '' }}">
                    <a href="{{ url('app/expenses/create') }}" class="menu-link">
                        <div>{{ __('Add Expense') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('expense-view')
                <li class="menu-item {{ $currentRouteName === 'app-view-expense' ? 'active' : '' }}">
                    <a href="{{ url('app/expenses') }}" class="menu-link">
                        <div>{{ __('View Expense') }}</div>
                    </a>
                </li>
                @endpermission
            </ul>
        </li>

        <!-- Income -->
        <li class="menu-item {{ isMenuActive('app-income', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-file-dollar"></i>
                <div>{{ __('Income') }}</div>
            </a>
            <ul class="menu-sub">
                @permission('income-view')
                <li class="menu-item {{ $currentRouteName === 'app-incomes' ? 'active' : '' }}">
                    <a href="{{ url('app/incomes/create') }}" class="menu-link">
                        <div>{{ __('Add Income') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('income-view')
                <li class="menu-item {{ $currentRouteName === 'app-view-income' ? 'active' : '' }}">
                    <a href="{{ url('app/incomes') }}" class="menu-link">
                        <div>{{ __('View Income') }}</div>
                    </a>
                </li>
                @endpermission
            </ul>
        </li>

        <!-- Issue -->
        <li class="menu-item {{ isMenuActive('issue', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-logout"></i>
                <div>{{ __('Issue') }}</div>
            </a>
            <ul class="menu-sub">
                @permission('issue-add')
                <li class="menu-item {{ $currentRouteName === 'issue.add-issue' ? 'active' : '' }}">
                    <a href="{{ url('/app/issue/add-issue') }}" class="menu-link">
                        <div>{{ __('Add Issue') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('issue-view')
                <li class="menu-item {{ $currentRouteName === 'issue.view-issue' ? 'active' : '' }}">
                    <a href="{{ url('/app/issue/view-issue') }}" class="menu-link">
                        <div>{{ __('View Issue') }}</div>
                    </a>
                </li>
                @endpermission
            </ul>
        </li>

        <!-- Purchase -->
        <li class="menu-item {{ isMenuActive('purchase', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-shopping-cart"></i>
                <div>{{ __('Purchase') }}</div>
            </a>
            <ul class="menu-sub">
                @permission('purchase-add')
                <li class="menu-item {{ $currentRouteName === 'purchase.add-purchase' ? 'active' : '' }}">
                    <a href="{{ url('/app/purchase/add-purchase') }}" class="menu-link">
                        <div>{{ __('Add Purchase') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('purchase-view')
                <li class="menu-item {{ $currentRouteName === 'purchase.view-purchase' ? 'active' : '' }}">
                    <a href="{{ url('/app/purchase/view-purchase') }}" class="menu-link">
                        <div>{{ __('View Purchase') }}</div>
                    </a>
                </li>
                @endpermission
            </ul>
        </li>

        <!-- Report -->
        <li class="menu-item {{ isMenuActive('app-report', $currentRouteName) }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-report"></i>
                <div>{{ __('Report') }}</div>
            </a>
            <ul class="menu-sub">
                @permission('bus-helper-report')
                <li class="menu-item {{ $currentRouteName === 'helper-list' ? 'active' : '' }}">
                    <a href="{{ url('app/helper-list') }}" class="menu-link">
                        <div>{{ __('Helper List') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('driver-list')
                <li class="menu-item {{ $currentRouteName === 'driver-list' ? 'active' : '' }}">
                    <a href="{{ url('app/driver-list') }}" class="menu-link">
                        <div>{{ __('Driver List') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('expense-report-view')
                <li class="menu-item {{ $currentRouteName === 'expense-report' ? 'active' : '' }}">
                    <a href="{{ url('app/expense-report') }}" class="menu-link">
                        <div>{{ __('Expense Report') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('employee-list-report-view')
                <li class="menu-item {{ $currentRouteName === 'employee-list-report' ? 'active' : '' }}">
                    <a href="{{ url('app/employee-list-report') }}" class="menu-link">
                        <div>{{ __('Employee List Report') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('monthly-bill-view')
                <li class="menu-item {{ $currentRouteName === 'monthly-bill' ? 'active' : '' }}">
                    <a href="{{ url('app/monthly-bill') }}" class="menu-link">
                        <div>{{ __('Monthly Bill') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'monthly-bill' ? 'active' : '' }}">
                    <a href="{{ url('app/bus-list') }}" class="menu-link">
                        <div>{{ __('Bus List') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ $currentRouteName === 'monthly-bill' ? 'active' : '' }}">
                    <a href="{{ url('app/trip-report') }}" class="menu-link">
                        <div>{{ __('Trip Report') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('punishment-report-view')
                <li class="menu-item {{ $currentRouteName === 'punishment-report' ? 'active' : '' }}">
                    <a href="{{ url('app/punishment-report') }}" class="menu-link">
                        <div>{{ __('Punishment Report') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('purchase-report-view')
                <li class="menu-item {{ $currentRouteName === 'purchase-report' ? 'active' : '' }}">
                    <a href="{{ url('app/purchase-report') }}" class="menu-link">
                        <div>{{ __('Purchase Report') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('issue-report-view')
                <li class="menu-item {{ $currentRouteName === 'issue-report' ? 'active' : '' }}">
                    <a href="{{ url('app/issue-report') }}" class="menu-link">
                        <div>{{ __('Issue Report') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('stock-report-view')
                <li class="menu-item {{ $currentRouteName === 'stock-report' ? 'active' : '' }}">
                    <a href="{{ url('app/stock-report') }}" class="menu-link">
                        <div>{{ __('Stock Report') }}</div>
                    </a>
                </li>
                @endpermission
                @permission('reward-report-view')
                <li class="menu-item {{ $currentRouteName === 'reward-report' ? 'active' : '' }}">
                    <a href="{{ url('app/reward-report') }}" class="menu-link">
                        <div>{{ __('Reward Report') }}</div>
                    </a>
                </li>
                @endpermission
                <li class="menu-item {{ $currentRouteName === 'brtc-bus-monthly-bill' ? 'active' : '' }}">
                    <a href="{{ url('app/brtc-bus-monthly-bill') }}" class="menu-link">
                        <div>{{ __('BRTC Bus Monthly Bill') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'daywise-trip-report' ? 'active' : '' }}">
                    <a href="{{ url('app/daywise-trip-report') }}" class="menu-link">
                        <div>{{ __('Daywise Trip Report') }}</div>
                    </a>
                </li>
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
                <li class="menu-item {{ $currentRouteName === 'driver-helper-assignment-report' ? 'active' : '' }}">
                    <a href="{{ route('driver-helper-assignment-report') }}" class="menu-link">
                        <div>{{ __('Driver Helper Assignment Report') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'driver-trip-report' ? 'active' : '' }}">
                    <a href="{{ route('driver-trip-report') }}" class="menu-link">
                        <div>{{ __('Driver Trip Report') }}</div>
                    </a>
                </li>
                @permission('salary-sheet-view')
                <li class="menu-item {{ $currentRouteName === 'salary-sheet' ? 'active' : '' }}">
                    <a href="{{ url('app/reports/salary-sheet') }}" class="menu-link">
                        <div>{{ __('Salary Sheet') }}</div>
                    </a>
                </li>
                @endpermission
            </ul>
        </li>

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
                <li class="menu-item {{ $currentRouteName === 'blood-group' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/blood-group') }}" class="menu-link">
                        <div>{{ __('Blood Group') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'bus-schedule-keyword' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/bus-schedule-keyword') }}" class="menu-link">
                        <div>{{ __('Bus Schedule Keyword') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'cache-clear' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/cache-clear') }}" class="menu-link">
                        <div>{{ __('Cache Clear') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'color' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/color') }}" class="menu-link">
                        <div>{{ __('Color') }}</div>
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
                <li class="menu-item {{ $currentRouteName === 'driver-type' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/driver-type') }}" class="menu-link">
                        <div>{{ __('Driver Type') }}</div>
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
                <li class="menu-item {{ $currentRouteName === 'fuel-type' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/fuel-type') }}" class="menu-link">
                        <div>{{ __('Fuel Type') }}</div>
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
                <li class="menu-item {{ $currentRouteName === 'issuing-authority' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/issuing-authority') }}" class="menu-link">
                        <div>{{ __('Issuing Authority') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'item' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/item') }}" class="menu-link">
                        <div>{{ __('Item') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'license-type' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/license-type') }}" class="menu-link">
                        <div>{{ __('License Type') }}</div>
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
                <li class="menu-item {{ $currentRouteName === 'punishment-type' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/punishment-type') }}" class="menu-link">
                        <div>{{ __('Punishment Type') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'violation-type' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/violation-type') }}" class="menu-link">
                        <div>{{ __('Violation Type') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'religion' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/religion') }}" class="menu-link">
                        <div>{{ __('Religion') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'reward-type' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/reward-type') }}" class="menu-link">
                        <div>{{ __('Reward Type') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'app-access-rules' ? 'active' : '' }}">
                    <a href="{{ url('rules') }}" class="menu-link">
                        <div>{{ __('Rules & Permissions') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'status' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/status') }}" class="menu-link">
                        <div>{{ __('Status') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'deployment-type' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/deployment-type') }}" class="menu-link">
                        <div>{{ __('Deployment Type') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'salary-configuration' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/salary-configuration') }}" class="menu-link">
                        <div>{{ __('Salary Configuration') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'stoppage' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/stoppage') }}" class="menu-link">
                        <div>{{ __('Stoppage') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'supplier' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/supplier') }}" class="menu-link">
                        <div>{{ __('Supplier') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'supplier_type' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/supplier_type') }}" class="menu-link">
                        <div>{{ __('Supplier Type') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'trip-time' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/trip-time') }}" class="menu-link">
                        <div>{{ __('Trip Time') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'employee-type' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/employee-type') }}" class="menu-link">
                        <div>{{ __('Employee Type') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'bus-type' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/bus-type') }}" class="menu-link">
                        <div>{{ __('Bus Type') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'bus-sub-type' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/bus-sub-type') }}" class="menu-link">
                        <div>{{ __('Bus Sub Type') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'bus-user' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/bus-user') }}" class="menu-link">
                        <div>{{ __('Bus User') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'unit' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/unit') }}" class="menu-link">
                        <div>{{ __('Unit') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'bus-route' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/bus-route') }}" class="menu-link">
                        <div>{{ __('Bus Route') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'users' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/users') }}" class="menu-link">
                        <div>{{ __('Users') }}</div>
                    </a>
                </li>
                <li class="menu-item {{ $currentRouteName === 'warehouse' ? 'active' : '' }}">
                    <a href="{{ url('app/settings/warehouse') }}" class="menu-link">
                        <div>{{ __('Warehouse') }}</div>
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
