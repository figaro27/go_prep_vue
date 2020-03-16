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



    @foreach($mealOrders as $i => $mealOrder)
      @php
      $reportSettings = $mealOrder->store->reportSettings;
      @endphp


      <div style="display:inline-block"> <div style="float:left;width:50%">
        <center>
      <img src="{{$logo}}"/ style="width:40vh;height:40vh"> 
      <h5 style="text-align: center"> {!! $mealOrder->html_title !!}
      </h5> @if ($reportSettings->lab_description) 
      <p> {!!
      $mealOrder->meal->description !!} </p> @endif
      
        @if ($reportSettings->lab_instructions)
        <p>
        {!! $mealOrder->meal->instructions !!}
        </p>
        @endif
        @if ($reportSettings->lab_expiration)
        <p>
        Consume Before: {!! $mealOrder->expirationDate !!}
        </p>
        @endif
        @if ($reportSettings->lab_customer)
        <p>
        <h6 style="text-align: center">Client: {!! $mealOrder->order->user->name !!}</h6>
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
      </center>
        </div>
        <div style="float:left;width:50%;height:auto">
        @if ($reportSettings->lab_macros and $mealOrder->meal->macros)
          Calories: {!! $mealOrder->meal->macros->calories !!}
          Proten: {!! $mealOrder->meal->macros->protein !!}
          Fat: {!! $mealOrder->meal->macros->fat !!}
          Carbs: {!! $mealOrder->meal->macros->carbs !!}
        @endif

        @if ($reportSettings->lab_nutrition)
          
          @if ($reportSettings->lab_expiration)
        <p>
        Consume Before: {!! $mealOrder->expirationDate !!}
        </p>
        @endif
            <div class="nutritionFacts" data-meal="{{ $mealOrder->json }}" style="transform:scale(1)"></div>
          
        @endif
      </div>

    
    @endforeach
  </div>


</html>
