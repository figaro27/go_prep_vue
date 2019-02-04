<!DOCTYPE html>
<html>
<head>
<title>GoPrep</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link rel="shortcut icon" href="{{{ asset('images/favicon.png') }}}">
</head>
<body>
    <div id="app">
        @yield("content")
    </div>
    <script src="https://js.stripe.com/v3/"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>