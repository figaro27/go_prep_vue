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
table {
  border: 1px solid #bebebe;
}

table th {
  padding-top: 2px;
  padding-bottom: 2px;
  text-align: center;
  background-color: #b2b2b2;
  color: white;
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
        <h4 class="mt-3">Order Details</h4>
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
            <p><strong>Total: ${{number_format($order->amount, 2)}} @if ($order->cashOrder) {{ $order->store->moduleSettings->cashOrderWording }} @endif</strong></p>
      </div>
      <div class="col-4 address">
        <h4>Customer</h4>
        <p>{{$order->user->name}}</p>
          <p>{{$order->user->details->address}}</p>
          <p>{{$order->user->details->city}},
          {{$order->user->details->state}}
          {{$order->user->details->zip}}</p>
          <p>{{$order->user->details->phone}}</p>
        </p>
      </div>
      <div class="col-4">
            <h4>{{ $order->store->details->name }}</h4>
            @if ($order->pickup === 0)
            <h5>DELIVERY</h5>
            @endif
            @if ($order->pickup === 1)
            <h5>PICKUP</h5>
            @endif
            @if ($params['dailyOrderNumbers'])
            <h5>Daily Order #{{$order['dailyOrderNumber']}}</h5>
            @endif
            <img src="{{$logo}}" style="zoom: 0.5; max-width: 50%; height: auto;" />
         <!-- <p>{{$order->store->details->domain}}.goprep.com</p> -->
      </div>
    </div>

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
        <tr>
          <table border="1">
            <tr>
              <td style="width:80%">
                @if ($order->store->settings->notesForCustomer != null)
                  <p>{!! nl2br($order->store->settings->notesForCustomer) !!}</p>
                @endif
              </td>
              <td style="width:20%">
                <table>
                  <tr><td class="center-text">Subtotal - {{ $subtotal }}</td></tr>
                  @if ($salesTax > 0)<tr><td class="center-text">Tax - {{ $salesTax }}</td></tr>@endif
                  @if ($mealPlanDiscount > 0)<tr><td class="center-text">Subscription Discount - {{ $mealPlanDiscount }}</td></tr>@endif
                  @if ($deliveryFee > 0)<tr><td class="center-text">Processing Fee - {{ $processingFee }}</td></tr>@endif
                  @if ($deliveryFee > 0)<tr><td class="center-text">Delivery Fee - {{ $deliveryFee }}</td></tr>@endif
                  @if ($coupon > 0)<tr><td class="center-text">Coupon - {{ $couponCode }} {{ $coupon }}</td></tr>@endif
                  <tr><td class="center-text">Total - {{ $amount }}</td></tr>
                  @if ($deposit != 100)<tr><td class="center-text">Paid - ${{number_format(($order->amount * $order->deposit)/100, 2)}}</td></tr>@endif
                  @if ($deposit != 100)<tr><td class="center-text">Balance - ${{number_format(($order->amount - ($order->amount * $order->deposit)/100), 2)}}</td></tr>@endif
                </table>
              </td>
            </tr>
          </table>
        </tr>
      </tbody>
    
    </table>


    @if (count($order->lineItemsOrders))
    <h2>Extras</h2>
      <table>
        <thead>
          <tr>
            <th>Quantity</th>
            <th>Item Name</th>
            <th>Price</th>
          </tr>
        </thead>
      <tbody>
          @foreach ($order->lineItemsOrders as $i => $lineItemOrder)
          <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
              <td>{{$lineItemOrder->quantity}}</td>
              <td>{!! $lineItemOrder->title !!}</td>
              <td>${{number_format($lineItemOrder->price * $lineItemOrder->quantity, 2)}}</td>
          </tr>
          @endforeach
      </tbody>
      </table>
    @endif



    @php
      $mealInstructions = 0
    @endphp

    @foreach ($order->items as $i => $item)
          @if ($item->instructions)
            @php
              $mealInstructions = 1
            @endphp
          @endif
      @endforeach


    @if ($mealInstructions)
    <br>
      @foreach ($order->items as $i => $item)
        @if ($item->instructions)
          <p><b>{!! $item->html_title !!}</b> - {{ $item->instructions }}</p>
        @endif
      @endforeach
    @endif

  </div>
</body>
</html>