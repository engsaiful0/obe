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
            <h3 class="text-white mb-1">Welcome to OBE Dashboard</h3>
            <p class="text-white-50 mb-0">OBE Management System (OBEMS) - Overview of all modules and statistics</p>
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
          
</div>
@endsection
