<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset(mix('/css/print.css')) }}">

  <script src="https://cdnjs.cloudflare.com/ajax/libs/babel-polyfill/7.0.0/polyfill.min.js"></script>

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

  <script>
    window.status = "ready";
  </script>
</body>

</html>
