<!DOCTYPE html>
<html>
<head>
    @if ($store != null)
    <title>{{ $store->details->name }} </title>
    @else
    <title>GoPrep</title>
    @endif

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,600" rel="stylesheet">
    <!-- <link rel="shortcut icon" href="{{{ config('app.url').'/images/favicon.png' }}}"> -->
    <script>
    window.app = {
      domain: "{{ config('app.domain') }}",
      url: "{{ config('app.url') }}",
      front_url: "{{ config('app.front_url') }}",
      stripe_key: "{{ config('app.stripe_key') }}",
      authorize: {
        login_id: "{{ config('services.authorize.login_id') }}",
        public_key: "{{ config('services.authorize.public_key') }}"
      }
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
    @if ($store != null)
        @if ($store->settings->gaCode)
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $store->settings->gaCode }}"></script>
            <script >
                window.dataLayer = window.dataLayer || [];
                function gtag() {
                  dataLayer.push(arguments);
                }
                gtag("js", new Date());

                gtag("config", "{{ $store->settings->gaCode }}");
            </script>
        @endif
    @endif
    <script src="https://js.stripe.com/v3/" async></script>
    <script crossorigin="anonymous" src="https://polyfill.io/v3/polyfill.min.js?features=default%2CIntersectionObserver%2CIntersectionObserverEntry"></script>
    <script src="{{ mix('/js/manifest.js') }}"></script>
    <script src="{{ mix('/js/vendor.js') }}"></script>
    <script src="{{ mix('/js/app.js') }}"></script>

    @if(config('app.env') === 'production')
    <script type="text/javascript"
        src="https://js.authorize.net/v1/Accept.js"
        charset="utf-8"
        defer>
    </script>
    @else
    <script type="text/javascript"
        src="https://jstest.authorize.net/v1/Accept.js"
        charset="utf-8"
        defer>
    </script>
    @endif
    </body>
</html>
