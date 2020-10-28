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
@foreach($orders as $i => $order)
<div style="height:100vh">
  <div style="margin-top:20px">
  <center>
    <img src="{{$logo}}"/ style="width:28vh;height:28vh;margin-bottom:8px">
    @if ($reportSettings->o_lab_customer)
    <p class="text-18" style="font-weight:bold;margin-top:5px">Client: {{ $order['firstName'] }} {{ $order['lastName'] }}</p>
    @endif
    @if ($reportSettings->o_lab_address)
    <p class="text-13" style="margin-top:5px">{{ $order['address'] }}, {{ $order['city'] }}, {{ $order['state'] }} {{ $order['zip'] }}</p>
    @endif
    @if ($reportSettings->o_lab_phone)
    <p class="text-13" style="margin-top:5px">{{ $order['phone'] }}</p>
    @endif
    @if ($reportSettings->o_lab_delivery)
    <p class="text-13" style="margin-top:5px;font-style:italic">{{ $order['deliveryInstructions'] }}</p>
    @endif

    <div style="margin-top:20px">
    @if ($reportSettings->o_lab_order_number)
    <p class="text-13" style="font-weight:bold;margin-top:5px">Order ID - {{ $order['orderNumber'] }}</p>
    @endif
    @if ($reportSettings->o_lab_daily_order_number)
    <p class="text-13" style="margin-top:5px">Daily Order - {{ $order['dailyOrderNumber'] }}</p>
    @endif
    @if ($reportSettings->o_lab_order_date)
    <p class="text-13" style="margin-top:5px">Order Date - {{ $order['created_at'] }}</p>
    @endif
    @if ($reportSettings->o_lab_delivery_date)
    <p class="text-13" style="margin-top:5px">{{ $order['transferType']}} Date - {{ $order['deliveryDate'] }}</p>
    @endif
    </div>

    <div style="margin-top:20px">
    @if ($reportSettings->o_lab_amount)
    <p class="text-13" style="font-weight:bold;margin-top:5px">Total - {{ $order['amount'] }}</p>
    @endif
    @if ($reportSettings->o_lab_balance)
    <p class="text-13" style="margin-top:5px">Balance - {{ $order['balance'] }}</p>
    @endif
  </div>

  <div style="margin-top:20px">
    @if ($reportSettings->website)
    <p class="text-13" style="margin-top:5px">{{ $website }}</p>
    @endif
    @if ($reportSettings->social)
    <p class="text-13" style="margin-top:5px">{{ $social }}</p>
    @endif
  </div>
  </center>
  </div>
</div>
  
@endforeach

</html>

