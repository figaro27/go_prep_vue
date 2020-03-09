<html>

<head>
  <base href="{{ url('/') }}">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset(mix('/css/print.css')) }}">

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
    @foreach($mealOrders as $i => $mealOrder)
      @php
      $reportSettings = $mealOrder->store->reportSettings;
      @endphp
      <div class="achy-breaky" style="max-height: 100vh; overflow: hidden;">

        @if ($reportSettings->lab_nutrition)

          <div style="width:50%">
        @endif

        <h5>
          {!! $mealOrder->html_title !!}
        </h5>
        <br>
        @if ($reportSettings->lab_description)
        <p>
        {!! $mealOrder->meal->description !!}
        </p>
        @endif
        
        @if ($reportSettings->lab_website)
        <p>
        {!! $mealOrder->store->settings->website !!}
        </p>
        @endif
        @if ($reportSettings->lab_social)
        <p>
        {!! $mealOrder->store->details->social !!}
        </p>
        @endif
        @if ($reportSettings->lab_expiration)
        <p>
        Consume Before: {!! $mealOrder->expirationDate !!}
        </p>
        @endif
        @if ($reportSettings->lab_instructions)
        <p>
        {!! $mealOrder->meal->instructions !!}
        </p>
        @endif
        @if ($reportSettings->lab_customer)
        <p>
        <h6>Client: {!! $mealOrder->order->user->name !!}</h6>
        </p>
        @endif
        

        @if ($reportSettings->lab_macros and $mealOrder->meal->macros)
          Calories: {!! $mealOrder->meal->macros->calories !!}
          Proten: {!! $mealOrder->meal->macros->protein !!}
          Fat: {!! $mealOrder->meal->macros->fat !!}
          Carbs: {!! $mealOrder->meal->macros->carbs !!}
        @endif

        @if ($reportSettings->lab_nutrition)
          </div>
          <div style="width:50%">
            <div class="nutritionFacts" data-meal="{{ $mealOrder->json }}"></div>
          </div>
        
        @endif


      </div>
    @endforeach
  </div>
</body>

</html>
