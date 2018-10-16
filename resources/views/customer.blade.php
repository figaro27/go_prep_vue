<!DOCTYPE html>
<html>
<head>
<title>Customer Panel</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div id="customerapp">
        @yield("content")
    </div>
    <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>