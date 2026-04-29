<!DOCTYPE html>
<html>
<head>
    <title>URL Test</title>
</head>
<body>
    <h1>URL Test Page</h1>
    
    <h2>Laravel URL Helper Results:</h2>
    <p><strong>url('/'):</strong> {{ url('/') }}</p>
    <p><strong>url('app/fee-management/monthly-report/data'):</strong> {{ url('app/fee-management/monthly-report/data') }}</p>
    
    <h2>JavaScript Test:</h2>
    <div id="js-results"></div>
    
    <script>
        // Test the data-base-url attribute
        var baseUrl = document.documentElement.getAttribute('data-base-url');
        document.getElementById('js-results').innerHTML = 
            '<p><strong>data-base-url attribute:</strong> ' + baseUrl + '</p>' +
            '<p><strong>window.location.origin:</strong> ' + window.location.origin + '</p>' +
            '<p><strong>window.location.pathname:</strong> ' + window.location.pathname + '</p>';
    </script>
</body>
</html>









