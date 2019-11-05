<!doctype html>
<html>
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
$cashOrder = $order->cashOrder;
$balance = $order->balance;
$brandColor = '#'.$order->store->settings->color;
@endphp

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset(mix('/css/print.css')) }}">
  <style>
    p,
    td {
      font-size: 14px;
    }

    th {
      font-size: 16px;
      font-weight: bold;
    }

    table,
    th,
    td {
      border: 0px solid #bebebe;
    }

    table th {
      color: white;
      background-color: @php echo $brandColor; @endphp !important;
    }

    table {
      margin: 0px;
      padding: 0px;
    }

    .center-text {
      text-align: center;
    }

  </style>
</head>


<body class="{{ $body_classes }}">
  <div id="print-area">

  <div class="row">
    <center>
      <div class="col-12 center-text">
          <h4 class="center-text bold-text" style="text-transform: uppercase;color: #3e3e3e">{{ $order->store->details->name }}</h4>
          <img style="zoom: 1" src="{{$logo}}" />
      </div>
    </center>
    </div>

    <table class="no-border table-heading" style="border-style:none">
      <thead>
        <tr>
          <th class="full-left-border-radius drop-shadow no-border" style="background: {{{$brandColor}}}">
            <div class="text-11">
              <span class="company-info company-table"></span>
              {{ $order->store->details->address }}<br>
              {{ $order->store->details->city }}, {{ $order->store->details->state }}, {{ $order->store->details->zip }}
            </div>
          </th>
          <th class="drop-shadow no-border">
            <div class="text-11">
              <span class="company-info company-table"></span>
            @if ($order->store->settings->website) {{ $order->store->settings->website }} 
            @else www{{$order->store->settings->domain}}.goprep.com<br>
            @endif
            {{ $order->store->user->email }}
          </th>
          <th class="full-right-border-radius drop-shadow no-border"><div class="text-11">
            <span class="company-info company-table"></span>
            {{ $order->store->user->details->phone }}
          </div>
        </th>
        </tr>
      </thead>
    </table>
    <br>

    <div class="row">
      <div class="col-4">
        <p><b>{{$order->user->name}}</b></p>
        <p>{{$order->user->details->address}}</p>
        <p>{{$order->user->details->city}},
          {{$order->user->details->state}}
          {{$order->user->details->zip}}</p>
        <p>{{$order->user->details->phone}}</p>
      </div>

      <div class="col-4">
        <p><b>Order Info</b></p>
        @if ($order->dailyOrderNumber)
        <p>Daily Order #{{$order->dailyOrderNumber}}</p>
        @endif
        <p>Order ID: {{$order->order_number}}</p>
        @if ($order->subscription)
        <p>Subscription #{{ $order->subscription->stripe_id }}</p>
        @endif
        <p>Order Date: {{$order->created_at->format('m/d/Y')}}</p>
        
      </div>

      <div class="col-4">
        <p><b>Delivery Info</b></p>
        @if ($order->pickup === 0)
        <p>Type: Delivery</p>
        @endif
        @if ($order->pickup === 1)
        <p>Type: Pickup</p>
        @endif
        @if ($order->pickup === 0)
        <p>Delivery Date: {{$order->delivery_date->format('m/d/Y')}}
        </p>
        @endif
        @if ($order->pickup === 1)
        <p>Pick Up Date: {{$order->delivery_date->format('m/d/Y')}}
        </p>
        @endif
        @if ($order->transferTime)
        @if ($order->pickup === 0)
        <p>Delivery Time: {{ $order->transferTime }}</p>
        @endif
        @if ($order->pickup === 1)
        <p>Pickup Time: {{ $order->transferTime }}</p>
        @endif
        @endif
      </div>

    </div>


    <br>
    <table class="no-border table-heading" style="border-style:none">
      <thead>
          <th class="top-left-border-radius drop-shadow no-border" style="text-align:center">Quantity</th>
          <th class="drop-shadow no-border">Item Name</th>
          <th class="top-right-border-radius drop-shadow no-border" style="text-align:center">Price</th>
      </thead>

      <tbody>

        @foreach($order->meal_package_items as $i => $mealPackageItem)
        <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
          <td>{{$mealPackageItem->quantity}}</td>
          <td>{{ $mealPackageItem->meal_package->title }}</td>
          <td style="text-align:center">${{number_format($mealPackageItem->meal_package->price * $mealPackageItem->quantity, 2)}}</td>
        </tr>



        @foreach($order->items as $i => $item)
        @if ($item->meal_package_order_id === $mealPackageItem->id)
        <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
          <td>{{$item->quantity}}</td>
          <td>{!! $item->html_title !!}</td>
          <td style="text-align:center">@if ($item->meal_package_title === null)
            ${{ number_format($item->unit_price, 2) }}
            @else
            In Package
            @endif</td>
        </tr>

        @endif
        @endforeach

        @endforeach
        @foreach($order->items as $i => $item)
        @if ($item->meal_package_order_id === null)
        <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
          <td style="text-align:center">{{$item->quantity}}</td>
          <td>{!! $item->html_title !!}</td>
          <td style="text-align:center">${{ number_format($item->price, 2) }}</td>
        </tr>

        @endif
        @endforeach

        @if (count($order->lineItemsOrders))
        @foreach ($order->lineItemsOrders as $i => $lineItemOrder)
        <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
          <td style="text-align:center">{{$lineItemOrder->quantity}}</td>
          <td>{!! $lineItemOrder->title !!}</td>
          <td style="text-align:center">${{number_format($lineItemOrder->price * $lineItemOrder->quantity, 2)}}</td>
        </tr>
        @endforeach
        @endif
      </tbody>
    </table>
    <table class="no-border" style="border-style:none">
      <tr>
        <td style="width:70%;padding-top:10px">
          @if ($order->store->settings->notesForCustomer != null)
          <p>{!! nl2br($order->store->settings->notesForCustomer) !!}</p>
          @endif
        </td>
        <td style="width:30%;margin-left:0px;padding-left:0px">
          <table border="0" style="border:0px;border-style:none;">
            <tr>
              <td style="border:none"><b>Subtotal</b></td>
              <td style="border:none;text-align:right;position:relative;right:20px">{{ $subtotal }}</td>
            </tr>
            @if ($order->mealPlanDiscount > 0)<tr>
              <td style="border:none"><b>Subscription Discount</b></td>
              <td style="border:none;text-align:right;position:relative;right:20px">{{ $mealPlanDiscount }}</td>
            </tr>@endif
            @if ($order->salesTax > 0)<tr>
              <td style="border:none"><b>Sales Tax</b></td>
              <td style="border:none;text-align:right;position:relative;right:20px">{{ $salesTax }}</td>
            </tr>@endif
            @if ($order->processingFee > 0)<tr>
              <td style="border:none"><b>Processing Fee</b></td>
              <td style="border:none;text-align:right;position:relative;right:20px">{{ $processingFee }}</td>
            </tr>@endif
            @if ($order->deliveryFee > 0)<tr>
              <td style="border:none"><b>Delivery Fee</b></td>
              <td style="border:none;text-align:right;position:relative;right:20px">{{ $deliveryFee }}</td>
            </tr>@endif
            @if ($order->couponReduction > 0)<tr>
              <td style="border:none"><b>Coupon</b></td>
              <td style="border:none;text-align:right;position:relative;right:20px">({{ $couponCode }}) {{ $coupon }}</td>
            </tr>@endif
            <tr>
              <td style="border:none"><b>Total</b></td>
              <td style="border:none;text-align:right;position:relative;right:20px">{{ $amount }}</td>
            </tr>
            @if ($order->balance > 0)<tr>
              <td style="border:none"><b>Paid</b></td>
              <td style="border:none;text-align:right;position:relative;right:20px">${{number_format($order->amount - $order->balance, 2)}}</td>
            </tr>@endif
            @if ($order->balance > 0)<tr>
              <td style="border:none"><b>Balance</b></td>
              <td style="border:none;text-align:right;position:relative;right:20px">${{number_format($order->balance, 2)}}</td>
            </tr>@endif
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
</div>
</body>

</html>