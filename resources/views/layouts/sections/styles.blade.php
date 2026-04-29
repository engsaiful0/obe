<!-- BEGIN: Theme CSS -->
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

<!-- Vendor Fonts CSS -->
<link rel="stylesheet" href="{{ asset('assets/vendor/fonts/tabler-icons.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icons.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}">

<!-- Core CSS -->
<link rel="stylesheet" href="{{ asset('assets/vendor/css' . $configData['rtlSupport'] . '/core' . ($configData['style'] !== 'light' ? '-' . $configData['style'] : '') . '.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/css' . $configData['rtlSupport'] . '/' . $configData['theme'] . ($configData['style'] !== 'light' ? '-' . $configData['style'] : '') . '.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}">

<!-- Vendor Styles -->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}">

<!-- Page Styles -->
@yield('vendor-style')
@yield('page-style')
