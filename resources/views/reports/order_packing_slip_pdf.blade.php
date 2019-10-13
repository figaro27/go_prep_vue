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
        <p>{{$order->user->name}}</p>
          <p>{{$order->user->details->address}}</p>
          <p>{{$order->user->details->city}},
          {{$order->user->details->state}}
          {{$order->user->details->zip}}</p>
          <p>{{$order->user->details->phone}}</p>
      </div>
            <div class="col-4">
              <img src="{{$logo}}" style="zoom: 0.5; max-width: 50%; height: auto;" />
            <h6>{{ $order->store->details->name }}</h6>
            @if ($order->pickup === 0)
            <h6>DELIVERY</h6>
            @endif
            @if ($order->pickup === 1)
            <h6>PICKUP</h6>
            @endif
      </div>
    <div class="col-4 address">
            @if ($params['dailyOrderNumbers'])
            <h5>Daily Order #{{$order['dailyOrderNumber']}}</h5>
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
        <tr>
          <table border="1">
            <tr>
              <td style="width:81%;margin-right:0px;padding-right:0px">
                @if ($order->store->settings->notesForCustomer != null)
                  <p>{!! nl2br($order->store->settings->notesForCustomer) !!}</p>
                @endif
              </td>
              <td style="width:19%;margin-left:0px;padding-left:0px">
                <table border="0" style="margin-right:0px;padding-right:0px">
                  <tr><td><b>Subtotal</b></td><td style="margin-right:0px;padding-right:0px">{{ $subtotal }}</td></tr>
                  @if ($salesTax > 0)<tr><td><b>Tax</b></td><td style="margin-right:0px;padding-right:0px">{{ $salesTax }}</td></tr>@endif
                  @if ($mealPlanDiscount > 0)<tr><td><b>Subscription Discount</b></td><td style="margin-right:0px;padding-right:0px">{{ $mealPlanDiscount }}</td></tr>@endif
                  @if ($deliveryFee > 0)<tr><td><b>Processing Fee</b></td><td style="margin-right:0px;padding-right:0px">{{ $processingFee }}</td></tr>@endif
                  @if ($deliveryFee > 0)<tr><td><b>Delivery Fee</b></td><td style="margin-right:0px;padding-right:0px">{{ $deliveryFee }}</td></tr>@endif
                  @if ($coupon > 0)<tr><td><b>Coupon</b></td><td style="margin-right:0px;padding-right:0px">{{ $couponCode }} {{ $coupon }}</td></tr>@endif
                  <tr><td><b>Total</b></td><td style="margin-right:0px;padding-right:0px">{{ $amount }}</td></tr>
                  @if ($deposit != 100)<tr><td><b>Paid</b></td><td style="margin-right:0px;padding-right:0px">${{number_format(($order->amount * $order->deposit)/100, 2)}}</td></tr>@endif
                  @if ($deposit != 100)<tr><td><b>Balance</b></td><td style="margin-right:0px;padding-right:0px">${{number_format(($order->amount - ($order->amount * $order->deposit)/100), 2)}}</td></tr>@endif
                </table>
              </td>
            </tr>
          </table>
        </tr>
      </tbody>
    
    </table>
    <br>
      @foreach ($order->items as $i => $item)
        @if ($item->instructions)
          <p><b>{!! $item->html_title !!}</b> - {{ $item->instructions }}</p>
        @endif
      @endforeach
  </div>
</body>
</html>