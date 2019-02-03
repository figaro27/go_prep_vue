<!DOCTYPE html>
<html>
<head>
<title>Customer Panel</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="shortcut icon" href="{{{ asset('images/favicon.png') }}}">
</head>
<body class="customer">
    <div id="customerapp">
        @yield("content")
    </div>
    <script src="https://js.stripe.com/v3/"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>