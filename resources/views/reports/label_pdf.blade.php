<html>

<head>
  <base href="{{ url('/') }}">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset(mix('/css/print.css')) }}">

  <script src="https://polyfill.io/v3/polyfill.min.js"></script>

  <script
  src="https://browser.sentry-cdn.com/5.13.0/bundle.min.js"
  integrity="sha384-ePH2Cp6F+/PJbfhDWeQuXujAbpil3zowccx6grtsxOals4qYqJzCjeIa7W2UqunJ"
  crossorigin="anonymous"></script>

  <script>
    Sentry.init({ dsn: 'https://f4c0f997f48e43bb8842db6ee7bbefc9@sentry.io/1427294' });
  </script>


  <script
  src="https://code.jquery.com/jquery-2.2.4.min.js"
  integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="
  crossorigin="anonymous"></script>
  <script src="{{ asset(mix('/js/manifest.js')) }}"></script>
  <script src="{{ asset(mix('/js/vendor.js')) }}"></script>
  <script src="{{ asset(mix('/js/print.js')) }}"></script>
</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    <div class="unbreakable">
      <h1>
        {!! $mealOrder->html_title !!}
        </h1>
        <p>
        {!! $mealOrder->meal->description !!}
        </p><p>
        {!! $mealOrder->meal->instructions !!}
        </p><p>
        {!! $mealOrder->store->details->name !!}
        </p><p>
        {!! $mealOrder->store->settings->website !!}
        </p>

        @if ($params['labelsNutrition'] === 'nutrition')
          <div class="nutritionFacts" data-meal="{{ $mealOrder->json }}"></div>
        @endif
    </div>
  </div>
</body>

</html>
