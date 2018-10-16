<!DOCTYPE html>
<html>
<head>
<title>Admin Panel</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div id="adminapp">
        @yield("content")
    </div>
    <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>