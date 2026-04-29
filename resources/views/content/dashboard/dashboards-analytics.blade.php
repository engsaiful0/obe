@extends('layouts/layoutMaster')

@section('title', 'TMS Analytics Dashboard')

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/chartjs/chartjs.css') }}">
@endsection

@section('page-style')
  <style>
    .module-card {
      transition: transform 0.2s, box-shadow 0.2s;
      cursor: pointer;
    }
    .module-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }
    .stat-icon {
      width: 60px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 12px;
      font-size: 24px;
    }
    .timeline {
      position: relative;
      padding-left: 30px;
    }
    .timeline-item {
      position: relative;
      margin-bottom: 20px;
    }
    .timeline-item:not(:last-child)::before {
      content: '';
      position: absolute;
      left: -20px;
      top: 20px;
      width: 2px;
      height: calc(100% + 10px);
      background: #e7e7e7;
    }
    .timeline-marker {
      position: absolute;
      left: -25px;
      top: 0;
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: #007bff;
    }
    .module-overview {
      border-left: 4px solid;
      padding-left: 15px;
    }
  </style>
@endsection

@section('vendor-script')
  <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
@endsection

@section('page-script')
  <script>
    // Purchase Monthly Trend Chart
    const purchaseTrendData = @json($purchaseMonthlyTrend ?? []);
    if (purchaseTrendData.length > 0 && document.getElementById('purchaseTrendChart')) {
      const purchaseChartOptions = {
        chart: { type: 'area', height: 300, toolbar: { show: false } },
        series: [{ name: 'Purchase Amount', data: purchaseTrendData.map(function(d) { return parseFloat(d.amount || 0); }) }],
        xaxis: { categories: purchaseTrendData.map(function(d) { return d.month; }) },
        colors: ['#7367F0'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.3 } }
      };
      new ApexCharts(document.querySelector('#purchaseTrendChart'), purchaseChartOptions).render();
    }

    // Expense Monthly Trend Chart
    const expenseTrendData = @json($expenseMonthlyTrend ?? []);
    if (expenseTrendData.length > 0 && document.getElementById('expenseTrendChart')) {
      const expenseChartOptions = {
        chart: { type: 'line', height: 300, toolbar: { show: false } },
        series: [{ name: 'Expense Amount', data: expenseTrendData.map(function(d) { return parseFloat(d.amount || 0); }) }],
        xaxis: { categories: expenseTrendData.map(function(d) { return d.month; }) },
        colors: ['#EA5455'],
      };
      new ApexCharts(document.querySelector('#expenseTrendChart'), expenseChartOptions).render();
    }

    // Bus Status Chart
    const busActive = {{ $busStats['active'] ?? 0 }};
    const busMaintenance = {{ $busStats['maintenance'] ?? 0 }};
    const busInactive = {{ $busStats['inactive'] ?? 0 }};
    if (document.getElementById('busStatusChart')) {
      const busStatusOptions = {
        chart: { type: 'donut', height: 300 },
        series: [busActive, busMaintenance, busInactive],
        labels: ['Active', 'Maintenance', 'Inactive'],
        colors: ['#28C76F', '#FF9F43', '#EA5455'],
      };
      new ApexCharts(document.querySelector('#busStatusChart'), busStatusOptions).render();
    }

    // Expense by Category Chart
    const expenseCategoryData = @json($expenseByCategory ?? []);
    if (expenseCategoryData.length > 0 && document.getElementById('expenseCategoryChart')) {
      const expenseCategoryOptions = {
        chart: { type: 'bar', height: 300, toolbar: { show: false } },
        series: [{ name: 'Amount', data: expenseCategoryData.map(function(d) { return parseFloat(d.amount || 0); }) }],
        xaxis: { categories: expenseCategoryData.map(function(d) { return d.category; }) },
        colors: ['#00CFE8'],
      };
      new ApexCharts(document.querySelector('#expenseCategoryChart'), expenseCategoryOptions).render();
    }

    // Bus by Type Chart
    const busTypeData = @json($busByType ?? []);
    if (busTypeData.length > 0 && document.getElementById('busTypeChart')) {
      const busTypeOptions = {
        chart: { type: 'pie', height: 300 },
        series: busTypeData.map(function(d) { return d.count; }),
        labels: busTypeData.map(function(d) { return d.type; }),
        colors: ['#7367F0', '#28C76F', '#00CFE8', '#FF9F43', '#EA5455'],
      };
      new ApexCharts(document.querySelector('#busTypeChart'), busTypeOptions).render();
    }

    // Trip Monthly Trend Chart
    const tripTrendData = @json($tripMonthlyTrend ?? []);
    if (tripTrendData.length > 0 && document.getElementById('tripTrendChart')) {
      const tripChartOptions = {
        chart: { type: 'line', height: 300, toolbar: { show: false } },
        series: [
          { name: 'Trips', data: tripTrendData.map(function(d) { return parseInt(d.trips || 0); }) },
          { name: 'Distance (km)', data: tripTrendData.map(function(d) { return parseFloat(d.distance || 0); }) }
        ],
        xaxis: { categories: tripTrendData.map(function(d) { return d.month; }) },
        colors: ['#7367F0', '#28C76F'],
        yaxis: [
          { title: { text: 'Trips' } },
          { opposite: true, title: { text: 'Distance (km)' } }
        ]
      };
      new ApexCharts(document.querySelector('#tripTrendChart'), tripChartOptions).render();
    }
  </script>
@endsection

@section('content')
<!-- Welcome Section -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card bg-gradient-primary text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h3 class="text-white mb-1">Welcome to TMS Dashboard</h3>
            <p class="text-white-50 mb-0">Transport Management System - Overview of all modules and statistics</p>
          </div>
          <div class="text-end">
            <h4 class="text-white mb-0">{{ now()->format('d M Y') }}</h4>
            <small class="text-white-50">{{ now()->format('l') }}</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Key Statistics Row 1 -->
<div class="row mb-4">
          <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card module-card" onclick="window.location.href='{{ route('buses.index') }}'">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="card-text text-muted mb-1">Total Buses</p>
            <h3 class="card-title mb-0">{{ number_format($busStats['total'] ?? 0) }}</h3>
            <small class="text-success">
              <i class="ti ti-arrow-up me-1"></i>{{ $busStats['active'] ?? 0 }} Active
            </small>
          </div>
          <div class="stat-icon bg-label-primary">
            <i class="ti ti-bus text-primary"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

          <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card module-card" onclick="window.location.href='{{ route('buses.index') }}'">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="card-text text-muted mb-1">Total Drivers</p>
            <h3 class="card-title mb-0">{{ number_format($driverStats['total'] ?? 0) }}</h3>
            <small class="text-success">
              <i class="ti ti-check me-1"></i>{{ $driverStats['active'] ?? 0 }} Active
            </small>
          </div>
          <div class="stat-icon bg-label-success">
            <i class="ti ti-user text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card module-card" onclick="window.location.href='{{ route('app-purchase-view') }}'">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="card-text text-muted mb-1">Total Purchases</p>
            <h3 class="card-title mb-0">{{ number_format($purchaseStats['total'] ?? 0) }}</h3>
            <small class="text-info">
              <i class="ti ti-currency-taka me-1"></i>৳{{ number_format($purchaseStats['this_month_amount'] ?? 0, 2) }} This Month
            </small>
          </div>
          <div class="stat-icon bg-label-info">
            <i class="ti ti-shopping-cart text-info"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card module-card" onclick="window.location.href='{{ route('expenses.index') }}'">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="card-text text-muted mb-1">Total Expenses</p>
            <h3 class="card-title mb-0">{{ number_format($expenseStats['total'] ?? 0) }}</h3>
            <small class="text-warning">
              <i class="ti ti-currency-taka me-1"></i>৳{{ number_format($expenseStats['this_month_amount'] ?? 0, 2) }} This Month
            </small>
          </div>
          <div class="stat-icon bg-label-warning">
            <i class="ti ti-receipt text-warning"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Key Statistics Row 2 -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card module-card" onclick="window.location.href='{{ route('bus-helpers.index') }}'">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="card-text text-muted mb-1">Total Bus Helpers</p>
            <h3 class="card-title mb-0">{{ number_format($busHelperStats['total'] ?? 0) }}</h3>
            <small class="text-success">
              <i class="ti ti-check me-1"></i>{{ $busHelperStats['active'] ?? 0 }} Active
            </small>
          </div>
          <div class="stat-icon bg-label-secondary">
            <i class="ti ti-users text-secondary"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card module-card" onclick="window.location.href='{{ route('employees.view-employee') }}'">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="card-text text-muted mb-1">Total Employees</p>
            <h3 class="card-title mb-0">{{ number_format($employeeStats['total'] ?? 0) }}</h3>
            <small class="text-muted">{{ $employeeStats['this_month'] ?? 0 }} Added This Month</small>
          </div>
          <div class="stat-icon bg-label-info">
            <i class="ti ti-user-circle text-info"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card module-card" onclick="window.location.href='{{ route('app-issue-view') }}'">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="card-text text-muted mb-1">Total Issues</p>
            <h3 class="card-title mb-0">{{ number_format($issueStats['total'] ?? 0) }}</h3>
            <small class="text-primary">{{ $issueStats['this_month'] ?? 0 }} This Month</small>
          </div>
          <div class="stat-icon bg-label-primary">
            <i class="ti ti-package text-primary"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card module-card" onclick="window.location.href='{{ route('expenses.index') }}'">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="card-text text-muted mb-1">Total Damages</p>
            <h3 class="card-title mb-0">{{ number_format($damageStats['total'] ?? 0) }}</h3>
            <small class="text-danger">Qty: {{ number_format($damageStats['total_quantity'] ?? 0, 2) }}</small>
          </div>
          <div class="stat-icon bg-label-danger">
            <i class="ti ti-alert-triangle text-danger"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
  <!-- Purchase Trend Chart -->
  <div class="col-lg-6 col-12 mb-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Purchase Trend (Last 6 Months)</h5>
        <div class="dropdown">
          <button class="btn p-0" type="button" data-bs-toggle="dropdown">
            <i class="ti ti-dots-vertical"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="{{ route('app-purchase-view') }}">View All Purchases</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div id="purchaseTrendChart"></div>
        <div class="d-flex justify-content-between mt-3">
          <div>
            <small class="text-muted">This Month</small>
            <h6 class="mb-0">৳{{ number_format($purchaseStats['this_month_amount'] ?? 0, 2) }}</h6>
          </div>
          <div class="text-end">
            <small class="text-muted">Growth</small>
            <h6 class="mb-0 {{ ($purchaseGrowth ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
              <i class="ti ti-arrow-{{ ($purchaseGrowth ?? 0) >= 0 ? 'up' : 'down' }}"></i>
              {{ number_format(abs($purchaseGrowth ?? 0), 1) }}%
            </h6>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Expense Trend Chart -->
  <div class="col-lg-6 col-12 mb-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Expense Trend (Last 6 Months)</h5>
        <div class="dropdown">
          <button class="btn p-0" type="button" data-bs-toggle="dropdown">
            <i class="ti ti-dots-vertical"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="{{ route('expenses.index') }}">View All Expenses</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div id="expenseTrendChart"></div>
        <div class="d-flex justify-content-between mt-3">
          <div>
            <small class="text-muted">This Month</small>
            <h6 class="mb-0">৳{{ number_format($expenseStats['this_month_amount'] ?? 0, 2) }}</h6>
          </div>
          <div class="text-end">
            <small class="text-muted">Growth</small>
            <h6 class="mb-0 {{ ($expenseGrowth ?? 0) >= 0 ? 'text-danger' : 'text-success' }}">
              <i class="ti ti-arrow-{{ ($expenseGrowth ?? 0) >= 0 ? 'up' : 'down' }}"></i>
              {{ number_format(abs($expenseGrowth ?? 0), 1) }}%
            </h6>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Module Overview Row -->
<div class="row mb-4">
  <!-- Bus Status & Type -->
  <div class="col-lg-6 col-12 mb-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Bus Status Distribution</h5>
      </div>
      <div class="card-body">
        <div id="busStatusChart"></div>
      </div>
    </div>
  </div>

  <div class="col-lg-6 col-12 mb-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Buses by Type</h5>
      </div>
      <div class="card-body">
        <div id="busTypeChart"></div>
      </div>
    </div>
  </div>
</div>

<!-- Expense by Category -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Top Expense Categories</h5>
      </div>
      <div class="card-body">
        <div id="expenseCategoryChart"></div>
      </div>
    </div>
  </div>
</div>

<!-- Module Information Cards -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">System Modules Overview</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <!-- Bus Management -->
          <div class="col-md-6 col-lg-4">
            <div class="module-overview border-primary">
              <div class="d-flex align-items-center mb-2">
                <i class="ti ti-bus text-primary me-2" style="font-size: 20px;"></i>
                <h6 class="mb-0">Bus Management</h6>
              </div>
              <p class="text-muted small mb-2">Manage fleet, bus details, registration, insurance, and maintenance schedules.</p>
              <div class="d-flex justify-content-between">
                <span class="badge bg-label-primary">{{ $busStats['total'] ?? 0 }} Buses</span>
                <a href="{{ route('buses.index') }}" class="btn btn-sm btn-outline-primary">View</a>
              </div>
            </div>
          </div>

          <!-- Driver Management -->
          <div class="col-md-6 col-lg-4">
            <div class="module-overview border-success">
              <div class="d-flex align-items-center mb-2">
                <i class="ti ti-user text-success me-2" style="font-size: 20px;"></i>
                <h6 class="mb-0">Driver Management</h6>
              </div>
              <p class="text-muted small mb-2">Track driver information, licenses, assignments, and performance records.</p>
              <div class="d-flex justify-content-between">
                <span class="badge bg-label-success">{{ $driverStats['total'] ?? 0 }} Drivers</span>
                <a href="{{ route('drivers.index') }}" class="btn btn-sm btn-outline-success">View</a>
              </div>
            </div>
          </div>

          <!-- Purchase Management -->
          <div class="col-md-6 col-lg-4">
            <div class="module-overview border-info">
              <div class="d-flex align-items-center mb-2">
                <i class="ti ti-shopping-cart text-info me-2" style="font-size: 20px;"></i>
                <h6 class="mb-0">Purchase Management</h6>
              </div>
              <p class="text-muted small mb-2">Record and manage purchase orders, suppliers, and inventory items.</p>
              <div class="d-flex justify-content-between">
                <span class="badge bg-label-info">{{ $purchaseStats['total'] ?? 0 }} Purchases</span>
                <a href="{{ route('app-purchase-view') }}" class="btn btn-sm btn-outline-info">View</a>
              </div>
            </div>
          </div>

          <!-- Issue Management -->
          <div class="col-md-6 col-lg-4">
            <div class="module-overview border-primary">
              <div class="d-flex align-items-center mb-2">
                <i class="ti ti-package text-primary me-2" style="font-size: 20px;"></i>
                <h6 class="mb-0">Issue Management</h6>
              </div>
              <p class="text-muted small mb-2">Track items issued to employees with quantity and date records.</p>
              <div class="d-flex justify-content-between">
                <span class="badge bg-label-primary">{{ $issueStats['total'] ?? 0 }} Issues</span>
                <a href="{{ route('app-issue-view') }}" class="btn btn-sm btn-outline-primary">View</a>
              </div>
            </div>
          </div>

          <!-- Damage Management -->
          <div class="col-md-6 col-lg-4">
            <div class="module-overview border-danger">
              <div class="d-flex align-items-center mb-2">
                <i class="ti ti-alert-triangle text-danger me-2" style="font-size: 20px;"></i>
                <h6 class="mb-0">Damage Management</h6>
              </div>
              <p class="text-muted small mb-2">Record damaged items with quantity, reason, and approximate value.</p>
              <div class="d-flex justify-content-between">
                <span class="badge bg-label-danger">{{ $damageStats['total'] ?? 0 }} Damages</span>
                <a href="{{ route('app-damage-view') }}" class="btn btn-sm btn-outline-danger">View</a>
              </div>
            </div>
          </div>

          <!-- Expense Management -->
          <div class="col-md-6 col-lg-4">
            <div class="module-overview border-warning">
              <div class="d-flex align-items-center mb-2">
                <i class="ti ti-receipt text-warning me-2" style="font-size: 20px;"></i>
                <h6 class="mb-0">Expense Management</h6>
              </div>
              <p class="text-muted small mb-2">Manage and categorize expenses with detailed reporting and analytics.</p>
              <div class="d-flex justify-content-between">
                <span class="badge bg-label-warning">{{ $expenseStats['total'] ?? 0 }} Expenses</span>
                <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-outline-warning">View</a>
              </div>
            </div>
          </div>

          <!-- Reward Management -->
          <div class="col-md-6 col-lg-4">
            <div class="module-overview border-success">
              <div class="d-flex align-items-center mb-2">
                <i class="ti ti-award text-success me-2" style="font-size: 20px;"></i>
                <h6 class="mb-0">Reward Management</h6>
              </div>
              <p class="text-muted small mb-2">Track and manage employee rewards and recognition programs.</p>
              <div class="d-flex justify-content-between">
                <span class="badge bg-label-success">{{ $rewardStats['total'] ?? 0 }} Rewards</span>
                <a href="{{ route('rewards.index') }}" class="btn btn-sm btn-outline-success">View</a>
              </div>
            </div>
          </div>

          <!-- Punishment Management -->
          <div class="col-md-6 col-lg-4">
            <div class="module-overview border-danger">
              <div class="d-flex align-items-center mb-2">
                <i class="ti ti-ban text-danger me-2" style="font-size: 20px;"></i>
                <h6 class="mb-0">Punishment Management</h6>
              </div>
              <p class="text-muted small mb-2">Record violations, fines, and disciplinary actions for drivers/assistants.</p>
              <div class="d-flex justify-content-between">
                <span class="badge bg-label-danger">{{ $punishmentStats['total'] ?? 0 }} Punishments</span>
                <a href="{{ route('punishments.index') }}" class="btn btn-sm btn-outline-danger">View</a>
              </div>
            </div>
          </div>

          <!-- Additional Modules -->
          <div class="col-md-6 col-lg-4">
            <div class="module-overview border-secondary">
              <div class="d-flex align-items-center mb-2">
                <i class="ti ti-users text-secondary me-2" style="font-size: 20px;"></i>
                <h6 class="mb-0">Bus Helper Management</h6>
              </div>
              <p class="text-muted small mb-2">Manage bus helper staff, assignments, and performance tracking.</p>
              <div class="d-flex justify-content-between">
                <span class="badge bg-label-secondary">{{ $busHelperStats['total'] ?? 0 }} Bus Helpers</span>
                <a href="{{ route('bus-helpers.index') }}" class="btn btn-sm btn-outline-secondary">View</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Recent Activities -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Recent Activities</h5>
      </div>
      <div class="card-body">
        <div class="timeline">
          @forelse($recentActivities ?? [] as $activity)
            <div class="timeline-item">
              <div class="timeline-marker bg-{{ $activity['color'] ?? 'primary' }}"></div>
              <div class="timeline-content">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h6 class="mb-1">
                      <i class="{{ $activity['icon'] ?? 'ti ti-circle' }} me-2 text-{{ $activity['color'] ?? 'primary' }}"></i>
                      {{ $activity['message'] ?? 'Activity' }}
                    </h6>
                    <small class="text-muted">{{ $activity['date'] ? \Carbon\Carbon::parse($activity['date'])->diffForHumans() : 'N/A' }}</small>
                  </div>
                  @if(isset($activity['url']))
                    <a href="{{ $activity['url'] }}" class="btn btn-sm btn-outline-{{ $activity['color'] ?? 'primary' }}">
                      View
                    </a>
                  @endif
                </div>
              </div>
            </div>
          @empty
            <div class="text-center py-4">
              <i class="ti ti-inbox" style="font-size: 3rem; color: #6c757d;"></i>
              <p class="text-muted mt-2">No recent activities</p>
            </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Financial Summary -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Financial Summary</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-lg-3 col-md-6">
            <div class="card border-primary">
              <div class="card-body text-center">
                <i class="ti ti-trending-up text-primary" style="font-size: 32px;"></i>
                <h5 class="mt-2 mb-1 text-primary">৳{{ number_format($financialSummary['total_revenue'] ?? 0, 2) }}</h5>
                <small class="text-muted">Total Revenue</small>
                <div class="mt-2">
                  <small class="text-success">
                    This Month: ৳{{ number_format($financialSummary['this_month_revenue'] ?? 0, 2) }}
                  </small>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="card border-danger">
              <div class="card-body text-center">
                <i class="ti ti-trending-down text-danger" style="font-size: 32px;"></i>
                <h5 class="mt-2 mb-1 text-danger">৳{{ number_format($financialSummary['total_expenses'] ?? 0, 2) }}</h5>
                <small class="text-muted">Total Expenses</small>
                <div class="mt-2">
                  <small class="text-danger">
                    This Month: ৳{{ number_format($financialSummary['this_month_expenses'] ?? 0, 2) }}
                  </small>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="card border-{{ ($financialSummary['net_profit'] ?? 0) >= 0 ? 'success' : 'danger' }}">
              <div class="card-body text-center">
                <i class="ti ti-chart-line text-{{ ($financialSummary['net_profit'] ?? 0) >= 0 ? 'success' : 'danger' }}" style="font-size: 32px;"></i>
                <h5 class="mt-2 mb-1 text-{{ ($financialSummary['net_profit'] ?? 0) >= 0 ? 'success' : 'danger' }}">
                  ৳{{ number_format($financialSummary['net_profit'] ?? 0, 2) }}
                </h5>
                <small class="text-muted">Net Profit/Loss</small>
                <div class="mt-2">
                  <small class="text-{{ ($financialSummary['this_month_profit'] ?? 0) >= 0 ? 'success' : 'danger' }}">
                    This Month: ৳{{ number_format($financialSummary['this_month_profit'] ?? 0, 2) }}
                  </small>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="card border-warning">
              <div class="card-body text-center">
                <i class="ti ti-alert-triangle text-warning" style="font-size: 32px;"></i>
                <h5 class="mt-2 mb-1 text-warning">৳{{ number_format($financialSummary['total_damage_cost'] ?? 0, 2) }}</h5>
                <small class="text-muted">Total Damage Cost</small>
                <div class="mt-2">
                  <small class="text-muted">
                    {{ number_format($damageStats['total'] ?? 0) }} Incidents
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Trip Statistics & Inventory -->
<div class="row mb-4">
  <!-- Trip Statistics -->
  <div class="col-lg-6 col-12 mb-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Trip Statistics</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-6">
            <div class="text-center p-3 bg-label-primary rounded">
              <h3 class="mb-1">{{ number_format($tripStats['total'] ?? 0) }}</h3>
              <small class="text-muted">Total Trips</small>
            </div>
          </div>
          <div class="col-6">
            <div class="text-center p-3 bg-label-success rounded">
              <h3 class="mb-1">{{ number_format($tripStats['this_month'] ?? 0) }}</h3>
              <small class="text-muted">This Month</small>
            </div>
          </div>
          <div class="col-6">
            <div class="text-center p-3 bg-label-info rounded">
              <h3 class="mb-1">{{ number_format($tripStats['total_distance'] ?? 0, 2) }}</h3>
              <small class="text-muted">Total Distance (km)</small>
            </div>
          </div>
          <div class="col-6">
            <div class="text-center p-3 bg-label-warning rounded">
              <h3 class="mb-1">{{ number_format($tripStats['avg_distance'] ?? 0, 2) }}</h3>
              <small class="text-muted">Avg Distance (km)</small>
            </div>
          </div>
        </div>
        @if(isset($tripMonthlyTrend) && count($tripMonthlyTrend) > 0)
          <div class="mt-4">
            <div id="tripTrendChart"></div>
          </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Inventory Statistics -->
  <div class="col-lg-6 col-12 mb-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Inventory Statistics</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-6">
            <div class="text-center p-3 bg-label-success rounded">
              <h3 class="mb-1">{{ number_format($inventoryStats['total_purchased_quantity'] ?? 0, 2) }}</h3>
              <small class="text-muted">Total Purchased</small>
              <div class="mt-1">
                <small class="text-muted">This Month: {{ number_format($inventoryStats['this_month_purchased'] ?? 0, 2) }}</small>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="text-center p-3 bg-label-primary rounded">
              <h3 class="mb-1">{{ number_format($inventoryStats['total_issued_quantity'] ?? 0, 2) }}</h3>
              <small class="text-muted">Total Issued</small>
              <div class="mt-1">
                <small class="text-muted">This Month: {{ number_format($inventoryStats['this_month_issued'] ?? 0, 2) }}</small>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="text-center p-3 bg-label-danger rounded">
              <h3 class="mb-1">{{ number_format($inventoryStats['total_damaged_quantity'] ?? 0, 2) }}</h3>
              <small class="text-muted">Total Damaged</small>
            </div>
          </div>
          <div class="col-6">
            <div class="text-center p-3 bg-label-info rounded">
              <h3 class="mb-1">{{ number_format($itemStats['total'] ?? 0) }}</h3>
              <small class="text-muted">Total Items</small>
              <div class="mt-1">
                <small class="text-muted">Warehouses: {{ number_format($itemStats['total_warehouses'] ?? 0) }}</small>
              </div>
            </div>
          </div>
        </div>
        @if(isset($issueItemsStats))
          <div class="mt-3 p-3 bg-light rounded">
            <div class="d-flex justify-content-between align-items-center">
              <span class="text-muted">Issued This Month:</span>
              <strong>{{ number_format($issueItemsStats['this_month_quantity'] ?? 0, 2) }} units</strong>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Additional Statistics Row -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card text-center">
      <div class="card-body">
        <i class="ti ti-calendar text-primary" style="font-size: 32px;"></i>
        <h5 class="mt-2 mb-1">{{ number_format($tripStats['this_month'] ?? 0) }}</h5>
        <small class="text-muted">Trips This Month</small>
        <div class="mt-2">
          <small class="text-info">Distance: {{ number_format($tripStats['this_month_distance'] ?? 0, 2) }} km</small>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card text-center">
      <div class="card-body">
        <i class="ti ti-building-warehouse text-info" style="font-size: 32px;"></i>
        <h5 class="mt-2 mb-1">{{ number_format($supplierStats['total'] ?? 0) }}</h5>
        <small class="text-muted">Total Suppliers</small>
        <div class="mt-2">
          <small class="text-success">Active: {{ number_format($supplierStats['active'] ?? 0) }}</small>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card text-center">
      <div class="card-body">
        <i class="ti ti-file-invoice text-warning" style="font-size: 32px;"></i>
        <h5 class="mt-2 mb-1">{{ number_format($monthlyBillStats['total'] ?? 0) }}</h5>
        <small class="text-muted">Monthly Bills</small>
        <div class="mt-2">
          <small class="text-primary">This Month: {{ number_format($monthlyBillStats['this_month'] ?? 0) }}</small>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card text-center">
      <div class="card-body">
        <i class="ti ti-calendar-check text-success" style="font-size: 32px;"></i>
        <h5 class="mt-2 mb-1">{{ number_format($scheduleStats['total'] ?? 0) }}</h5>
        <small class="text-muted">Bus Schedules</small>
        <div class="mt-2">
          <small class="text-success">Active: {{ number_format($scheduleStats['active'] ?? 0) }}</small>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Quick Stats Summary -->
<div class="row">
  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card text-center">
      <div class="card-body">
        <i class="ti ti-currency-taka text-primary" style="font-size: 32px;"></i>
        <h5 class="mt-2 mb-1">৳{{ number_format(($financialSummary['this_month_profit'] ?? 0), 2) }}</h5>
        <small class="text-muted">Net This Month</small>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card text-center">
      <div class="card-body">
        <i class="ti ti-award text-success" style="font-size: 32px;"></i>
        <h5 class="mt-2 mb-1">৳{{ number_format($rewardStats['total_amount'] ?? 0, 2) }}</h5>
        <small class="text-muted">Total Rewards</small>
        <div class="mt-1">
          <small class="text-muted">This Month: ৳{{ number_format($rewardStats['this_month_amount'] ?? 0, 2) }}</small>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card text-center">
      <div class="card-body">
        <i class="ti ti-ban text-danger" style="font-size: 32px;"></i>
        <h5 class="mt-2 mb-1">৳{{ number_format($punishmentStats['total_fine'] ?? 0, 2) }}</h5>
        <small class="text-muted">Total Fines</small>
        <div class="mt-1">
          <small class="text-muted">This Month: ৳{{ number_format($punishmentStats['this_month_fine'] ?? 0, 2) }}</small>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card text-center">
      <div class="card-body">
        <i class="ti ti-wallet text-info" style="font-size: 32px;"></i>
        <h5 class="mt-2 mb-1">৳{{ number_format($purchaseStats['total_due'] ?? 0, 2) }}</h5>
        <small class="text-muted">Total Due</small>
        <div class="mt-1">
          <small class="text-danger">Unpaid Purchases</small>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
