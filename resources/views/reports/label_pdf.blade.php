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

@php
$reportSettings = $mealOrder->store->reportSettings;
@endphp
</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    <div class="unbreakable">
      <h1>
        {!! $mealOrder->html_title !!}
        </h1>
        @if ($reportSettings->lab_description)
        <p>
        {!! $mealOrder->meal->description !!}
        </p>
        @endif
        @if ($reportSettings->lab_instructions)
        <p>
        {!! $mealOrder->meal->instructions !!}
        </p>
        @endif
        @if ($reportSettings->lab_customer)
        <p>
        {!! $mealOrder->store->details->name !!}
        </p>
        @endif
        @if ($reportSettings->lab_website)
        <p>
        {!! $mealOrder->store->settings->website !!}
        </p>
        @endif
        @if ($reportSettings->lab_social)
        <p>
        {!! $mealOrder->store->settings->social !!}
        </p>
        @endif
        @if ($reportSettings->lab_expiration)
        <p>
        Consume Before: {!! $mealOrder->expirationDate !!}
        </p>
        @endif
        @if ($reportSettings->lab_macros)
          Calories: {!! $mealOrder->meal->macros->calories !!}
          Proten: {!! $mealOrder->meal->macros->protein !!}
          Fat: {!! $mealOrder->meal->macros->fat !!}
          Carbs: {!! $mealOrder->meal->macros->carbs !!}
        @endif
        @if ($reportSettings->lab_nutrition)
          <div class="nutritionFacts" data-meal="{{ $mealOrder->json }}"></div>
        @endif
    </div>
  </div>
</body>

</html>
