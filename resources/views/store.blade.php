<!DOCTYPE html>
<html>
<head>
<title>Store Panel</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" >
</head>
<body>
    <div id="storeapp">
        @yield("content")
    </div>
    <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>