<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset('css/print.css') }}">
<style>

p, td {
  font-size: 14px;
}
th {
  font-size: 16px;
  font-weight: bold;
}
table, th, td {
  border: 1px solid #bebebe;
}

table th {
  text-align: center;
  background-color: #b2b2b2;
  color: white;
}

table {
  margin:0px;
  padding:0px;
}

.center-text {
  text-align: center;
}

</style>
</head>
@php
$subtotal = '$'.number_format($order->preFeePreDiscount, 2);
$mealPlanDiscount = '$'.number_format($order->mealPlanDiscount, 2);
$deliveryFee = '$'.number_format($order->deliveryFee, 2);
$processingFee = '$'.number_format($order->processingFee, 2);
$salesTax = '$'.number_format($order->salesTax, 2);
$coupon = '$'.number_format($order->couponReduction, 2);
$couponCode = $order->couponCode;
$amount = '$'.number_format($order->amount, 2);
$deposit = '$'.number_format($order->deposit, 2);
@endphp
<body class="{{ $body_classes }}">
  <div id="print-area">
    <div class="row">
      
      <div class="col-4 address">
        <p><b>{{$order->user->name}}</b></p>
          <p>{{$order->user->details->address}}</p>
          <p>{{$order->user->details->city}},
          {{$order->user->details->state}}
          {{$order->user->details->zip}}</p>
          <p>{{$order->user->details->phone}}</p>
      </div>
      <div class="col-4 address">
            @if ($order->pickup === 0)
            <p><b>DELIVERY</b></p>
            @endif
            @if ($order->pickup === 1)
            <p><b>PICKUP</b></p>
            @endif
            @if ($params['dailyOrderNumbers'])
            <p>Daily Order #{{$order['dailyOrderNumber']}}</p>
            @endif
            <p>Order ID - {{$order->order_number}}</p>
            @if ($order->subscription)
            <p>Subscription #{{ $order->subscription->stripe_id }}</p>
            @endif
            <p>Order Date: {{$order->created_at->format('D, m/d/Y')}}</p>
            @if ($order->pickup === 0)
            <p>Delivery Date: {{$order->delivery_date->format('D, m/d/Y')}} 
              @if ($order->transferTime)
                {{ $order->transferTime }}
              @endif
            </p>
            @endif
            @if ($order->pickup === 1)
            <p>Pick Up Date: {{$order->delivery_date->format('D, m/d/Y')}}
              @if ($order->transferTime)
                {{ $order->transferTime }}
              @endif
            </p>
            @endif
      </div>
      <div class="col-4">
        <div style="float:right">
        <p><b>{{ $order->store->details->name }}</b></p>
        <img style="zoom: 0.5;padding-right:50px" src="{{$logo}}"/>
      </div>
      </div>
      </div>
      <br><br>
    <table border="1">
      <thead>
        <tr>
          <th>Quantity</th>
          <th>Item Name</th>
          <th>Price</th>
        </tr>
      </thead>

      <tbody>
        @foreach ($order->items as $i => $item)
        <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
            <td class="center-text">{{$item->quantity}}</td>
            <td>{!! $item->html_title !!}</td>
            <td class="center-text">${{number_format($item->price, 2)}}</td>
        </tr>
        @endforeach
        @if (count($order->lineItemsOrders))
        @foreach ($order->lineItemsOrders as $i => $lineItemOrder)
          <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
              <td>{{$lineItemOrder->quantity}}</td>
              <td>{!! $lineItemOrder->title !!}</td>
              <td>${{number_format($lineItemOrder->price * $lineItemOrder->quantity, 2)}}</td>
          </tr>
          @endforeach
          @endif
          </tbody>
    </table>
          <table border="1">
            <tr>
              <td style="width:65%;margin-right:0px;padding-right:0px;padding-top:10px">
                @if ($order->store->settings->notesForCustomer != null)
                  <p>{!! nl2br($order->store->settings->notesForCustomer) !!}</p>
                @endif
              </td>
              <td style="width:35%;margin-left:0px;padding-left:0px">
                <table border="0" style="border:0px;border-style:none;border-collapse: collapse">
                  <tr><td style="border:none"><b>Subtotal</b></td><td style="border:none">{{ $subtotal }}</td></tr>
                  @if ($order->salesTax > 0)<tr><td style="border:none"><b>Tax</b></td><td style="border:none">{{ $salesTax }}</td></tr>@endif
                  @if ($order->mealPlanDiscount > 0)<tr><td style="border:none"><b>Subscription Discount</b></td><td style="border:none">{{ $mealPlanDiscount }}</td></tr>@endif
                  @if ($order->processingFee > 0)<tr><td style="border:none"><b>Processing Fee</b></td><td style="border:none">{{ $processingFee }}</td></tr>@endif
                  @if ($order->deliveryFee > 0)<tr><td style="border:none"><b>Delivery Fee</b></td><td style="border:none">{{ $deliveryFee }}</td></tr>@endif
                  @if ($order->couponReduction > 0)<tr><td style="border:none"><b>Coupon</b></td><td style="border:none">({{ $couponCode }}) {{ $coupon }}</td></tr>@endif
                  <tr><td style="border:none"><b>Total</b></td><td style="border:none">{{ $amount }}</td></tr>
                  @if ($order->deposit != 100)<tr><td style="border:none"><b>Paid</b></td><td style="border:none">${{number_format(($order->amount * $order->deposit)/100, 2)}}</td></tr>@endif
                  @if ($order->deposit != 100)<tr><td style="border:none"><b>Balance</b></td><td style="border:none">${{number_format(($order->amount - ($order->amount * $order->deposit)/100), 2)}}</td></tr>@endif
                </table>
              </td>
            </tr>
          </table>

      
    <br>
      @foreach ($order->items as $i => $item)
        @if ($item->instructions)
          <p><b>{!! $item->title !!}</b> - {{ $item->instructions }}</p>
        @endif
      @endforeach
  </div>
</body>
</html>