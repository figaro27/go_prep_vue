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
    <p class="text-18" style="font-weight:bold;margin-top:5px">Client: {{ $order['firstName'] }} {{ $order['lastName'] }}</p>
    <p class="text-13" style="margin-top:5px">{{ $order['address'] }}, {{ $order['city'] }}, {{ $order['state'] }} {{ $order['zip'] }}</p>
    <p class="text-13" style="margin-top:5px">{{ $order['phone'] }}</p>
    <p class="text-13" style="margin-top:5px;font-style:italic">{{ $order['deliveryInstructions'] }}</p>

    <div style="margin-top:20px">
    <p class="text-13" style="margin-top:5px">Order Date - {{ $order['created_at'] }}</p>
    <p class="text-13" style="font-weight:bold;margin-top:5px">Order ID - {{ $order['orderNumber'] }}</p>
    <p class="text-13" style="font-weight:bold;margin-top:5px">Daily Order - {{ $order['dailyOrderNumber'] }}</p>
    <p class="text-13" style="margin-top:5px">{{ $order['transferType']}} Date - {{ $order['deliveryDate'] }}</p>
    </div>

    <div style="margin-top:20px">
    <p class="text-13" style="margin-top:5px">Total - {{ $order['amount'] }}</p>
    <p class="text-15" style="margin-top:5px">Balance - {{ $order['balance'] }}</p>
  </div>
  </center>
  </div>
</div>
  
@endforeach

</html>

