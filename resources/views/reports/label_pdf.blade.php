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
font-size:10px !important;
}

.plus {
font-size:10px !important;
}
</style>
</head>


    @foreach($mealOrders as $i => $mealOrder)
    <div style="height:100vh">
      @php
      $labelSettings = $mealOrder->store->labelSettings;
      @endphp


      @if ($mealOrder->meal && $labelSettings->nutrition)
<div style="width:50%;float:left;margin-left:10px">
        @endif
  <center>
          @if ($labelSettings->logo)
      <img src="{{$mealOrder['logo']}}"/ style="width:25vh;margin-top:8px;margin-bottom:4px">
      @else
      <img src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=="/ style="width:25vh;height:15vh;margin-top:8px;margin-bottom:4px">
      @endif
      @if ($labelSettings->index)
      <p class="text-9">{{ $mealOrder->index }} of {{ $mealOrder->totalCount }}</p>
      @endif
      @if ($labelSettings->daily_order_number)
      <p class="text-11" style="font-weight:bold">Daily Order #{{ $mealOrder->order->dailyOrderNumber }}</p>
      @endif
      <p class="text-11" style="font-weight:bold"> {!! $mealOrder->meal ? $mealOrder->html_title : $mealOrder->title !!}
      </p> @if ($mealOrder->meal && $labelSettings->description)
      <p class="text-9"> {!!
      $mealOrder->meal->description !!} </p> @endif
      @if ($mealOrder->meal && $labelSettings->macros && $mealOrder->meal->macros)
      <p class="text-9">
          <b>Calories:</b> {!! $mealOrder->meal->macros->calories !!}&nbsp&nbsp
          <b>Protein:</b> {!! $mealOrder->meal->macros->protein !!}&nbsp&nbsp
          <b>Fat:</b> {!! $mealOrder->meal->macros->fat !!}&nbsp&nbsp
          <b>Carbs:</b> {!! $mealOrder->meal->macros->carbs !!}
        </p>
        @endif

        @if ($mealOrder->meal && $labelSettings->instructions)
        <p class="text-8">
        {!! $mealOrder->meal->instructions !!}
        </p>
        @endif
        @if ($mealOrder->meal && $labelSettings->expiration)
        <p class="text-8">
        Consume Before: {!! $mealOrder->expirationDate !!}
        </p>
        @endif
        @if ($labelSettings->packaged_by)
        <p class="text-7">
        Packaged By: 
          {{ $mealOrder->store->details->name }}, {{ $mealOrder->store->details->address }}, {{ $mealOrder->store->details->city }}, {{ $mealOrder->store->details->state }} {{ $mealOrder->store->details->zip }}
        </p>
        @endif
        @if ($labelSettings->packaged_on)
        <p class="text-7">
        Packaged On: {{ date('m/d/Y') }} 
        </p>
        @endif
  
 @if ($labelSettings->customer)

        <p class="text-11" style="font-weight:bold;margin-top:5px">Client: {!! $mealOrder->order->customer_name !!}</p>

        @endif

@if ($mealOrder->meal && $labelSettings->ingredients && strlen($mealOrder['ingredientList']) > 0)
<p class="text-8"><b>Ingredients:</b> {{ $mealOrder['ingredientList'] }}</p>
@endif
@if ($mealOrder->meal && $labelSettings->allergies && strlen($mealOrder['allergyList']) > 0)
<p class="text-8"><b>Allergens:</b> {{ $mealOrder['allergyList'] }}</p>
@endif
@if ($labelSettings->website)
        <p class="text-9"><b>
        {!! $mealOrder->store->settings->website !!}
        </b></p>
        @endif
        @if ($labelSettings->social)
        <p class="text-9"><b>
        {!! $mealOrder->store->details->social !!}
        </b></p>
        @endif
  </center>
        </div>
        @if ($mealOrder->meal && $labelSettings->nutrition)
        <div style="width:45%;float:left">

          @endif

        @if ($mealOrder->meal && $labelSettings->nutrition)

          <div class="nutritionFacts sm" data-meal="{{ $mealOrder->json }}" style="position:relative;left:20px;top:10px"></div>
      @if ($mealOrder->meal && ($labelSettings->nutrition || $labelSettings->macros))

      </div>
      @endif
      @endif
      </div>
    @endforeach



</html>

