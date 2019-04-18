<!DOCTYPE html>
<html>
<head>
<title>GoPrep</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ config('app.url').'/css/app.css' }}">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,600" rel="stylesheet">
    <link rel="shortcut icon" href="{{{ config('app.url').'/images/favicon.png' }}}">
    <script>
    window.app = {
      domain: "{{ config('app.domain') }}",
      url: "{{ config('app.url') }}",
      front_url: "{{ config('app.front_url') }}",
      stripe_key: "{{ config('app.stripe_key') }}"
    }
    </script>
</head>
<body>
        <script type="text/javascript">
            window._mfq = window._mfq || [];
            (function() {
                var mf = document.createElement("script");
                mf.type = "text/javascript"; mf.async = true;
                mf.src = "//cdn.mouseflow.com/projects/@php echo env('MOUSEFLOW') @endphp";
                document.getElementsByTagName("head")[0].appendChild(mf);
            })();
        </script>
    <div id="xs"></div>
    <div id="sm"></div>
    <div id="md"></div>
    <div id="lg"></div>

    <div id="app">
        @yield("content")
    </div>
    <script src="https://js.stripe.com/v3/"></script>
    <script crossorigin="anonymous" src="https://polyfill.io/v3/polyfill.min.js?features=default%2CIntersectionObserver%2CIntersectionObserverEntry"></script>
    <script src="{{ mix('/js/app.js') }}"></script>
    </body>
</html>