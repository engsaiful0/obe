@php
use Illuminate\Support\Facades\Vite;

$menuCollapsed = ($configData['menuCollapsed'] === 'layout-menu-collapsed') ? json_encode(true) : false;
@endphp
<!-- laravel style -->

 <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
<!-- beautify ignore:start -->
@if ($configData['hasCustomizer'])
  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
  <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
 @section('page-script')
  <script src="{{ asset('assets/vendor/js/template-customizer.js') }}"></script>
@endsection

@endif

  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  <!-- Config.js is now loaded in scripts.blade.php -->


@if ($configData['hasCustomizer'])
<script type="module">
 
</script>
@endif
