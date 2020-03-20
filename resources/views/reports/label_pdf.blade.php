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
<style>
li{
font-size:8px !important;

text-indent: 1% !important;

}
.plain {
position:relative !important;
margin-left:-30px !important;
}
</style>
</head>



    @foreach($mealOrders as $i => $mealOrder)
      @php
      $reportSettings = $mealOrder->store->reportSettings;
      @endphp


      @if ($reportSettings->lab_nutrition)
<div style="width:50%;float:left;margin-left:10px">
        @endif
  <center>
          @if ($reportSettings->lab_logo)
      <img src="{{$logo}}"/ style="width:32vh;height:32vh;margin-top:8px;margin-bottom:4px">
      @endif

      <p class="text-11" style="font-weight:bold"> {!! $mealOrder->html_title !!}
      </p> @if ($reportSettings->lab_description)
      <p> {!!
      $mealOrder->meal->description !!} </p> @endif

        @if ($reportSettings->lab_instructions)
        <p class="text-10">
        {!! $mealOrder->meal->instructions !!}
        </p>
        @endif
        @if ($reportSettings->lab_expiration)
        <p class="text-10">
        Consume Before: {!! $mealOrder->expirationDate !!}
        </p>
        @endif
             @if ($reportSettings->lab_website)
        <p class="text-9">
        {!! $mealOrder->store->settings->website !!}
        </p>
        @endif
        @if ($reportSettings->lab_social)
        <p class="text-9">
        {!! $mealOrder->store->details->social !!}
        </p>
        @endif
 @if ($reportSettings->lab_customer)

        <p class="text-13" style="font-weight:bold;margin-top:5px">Client: {!! $mealOrder->order->user->name !!}</p>

        @endif

  </center>
        </div>
        @if ($reportSettings->lab_nutrition || $reportSettings->lab_macros)
        <div style="width:45%;float:left">

          @endif
        @if ($reportSettings->lab_macros and $mealOrder->meal->macros)
          Calories: {!! $mealOrder->meal->macros->calories !!}
          Proten: {!! $mealOrder->meal->macros->protein !!}
          Fat: {!! $mealOrder->meal->macros->fat !!}
          Carbs: {!! $mealOrder->meal->macros->carbs !!}
        @endif

        @if ($reportSettings->lab_nutrition)

          <div class="nutritionFacts sm" data-meal="{{ $mealOrder->json }}" style="position:relative;left:20px;top:10px"></div>
      @if ($reportSettings->lab_nutrition || $reportSettings->lab_macros)

      </div>
      @endif
      @endif

    @endforeach



</html>
