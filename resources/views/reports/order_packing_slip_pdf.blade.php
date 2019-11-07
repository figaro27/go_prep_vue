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
$brandColor = $order->store->settings->color;
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

    table {
      border-bottom: 3px solid <?= $brandColor ?>;
    }

    table th, table tfoot {
      color: white;
      background: <?= $brandColor ?>;;
    }

    table {
      margin: 0px;
      padding: 0px;
    }

    .center-text {
      text-align: center;
    }

    .brandColor {
      background: <?= $brandColor ?>;;
      background-color: <?= $brandColor ?>;;
    }

  </style>
</head>


<body class="{{ $body_classes }}">
  <div id="print-area">

  <div class="row">
    <div class="col-4 center-text">
          <p class="center-text bold-text" style="text-transform: uppercase;color: #3e3e3e;padding-bottom:0px;margin-bottom:0px">Order Date: {{$order->created_at->format('m/d/Y')}}</p>

      </div>
    
    <center>
      <div class="col-4 center-text">
          <h4 class="center-text bold-text" style="text-transform: uppercase;color: #3e3e3e;padding-bottom:0px;margin-bottom:0px">{{ $order->store->details->name }}</h4>
          <img style="zoom: 1" src="{{$logo}}" />
      </div>
      
      <div class="col-4 center-text">
          <p class="center-text bold-text" style="text-transform: uppercase;color: #3e3e3e;padding-bottom:0px;margin-bottom:0px">Daily Order #{{$order->dailyOrderNumber}}</p>
          <p class="center-text bold-text" style="text-transform: uppercase;color: #3e3e3e;padding-bottom:0px;margin-bottom:0px">Order ID: {{$order->order_number}}</p>
      </div>
    </center>
    </div>
    <br>

    <!-- <table class="no-border table-heading" style="border-style:none">
      <thead>
        <tr>
          <th class="full-left-border-radius drop-shadow no-border">
            <div class="text-11 align-center">
              {{ $order->store->details->address }}<br>
              {{ $order->store->details->city }}, {{ $order->store->details->state }}, {{ $order->store->details->zip }}
            </div>
          </th>
          <th class="drop-shadow no-border">
            <div class="text-11 align-center" style="position:relative;right:27px">
            @if ($order->store->settings->website) {{ $order->store->settings->website }}<br>
            @else www{{$order->store->settings->domain}}.goprep.com<br>
            @endif
            {{ $order->store->user->email }}
          </th>
          <th class="full-right-border-radius drop-shadow no-border">
            <div class="text-11 align-center" style="position:relative;right:18px;top:8px">
            {{ $order->store->user->details->phone }}
          </div>
        </th>
        </tr>
      </thead>
    </table> -->

    <br>

    <div class="row">
      <div class="col-12 align-center">
        <p><b>{{$order->user->name}}</b></p>
        <p>{{$order->user->details->address}}</p>
        <p>{{$order->user->details->city}},
          {{$order->user->details->state}}
          {{$order->user->details->zip}}</p>
        <p>{{$order->user->details->phone}}</p>
      </div>

      <!-- <div class="col-4 align-center" style="position:relative;left:7px">
        <p><b>Order Info</b></p>
        @if ($order->store->modules->dailyOrderNumbers && $order->dailyOrderNumber)
        <p>Daily Order #{{$order->dailyOrderNumber}}</p>
        @endif
        <p>Order ID: {{$order->order_number}}</p>
        @if ($order->subscription)
        <p>Subscription #{{ $order->subscription->stripe_id }}</p>
        @endif
        <p>Order Date: {{$order->created_at->format('m/d/Y')}}</p>
        
      </div>

      <div class="col-4 align-center">
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
      </div> -->

    </div>


    <br>
    <table class="no-border table-heading" style="border-style:none;">
      <thead>
          <th class="top-left-border-radius drop-shadow no-border" style="text-align:center">Quantity</th>
          <th class="drop-shadow no-border">Item</th>
          <th class="top-right-border-radius drop-shadow no-border" style="text-align:center">Price</th>
      </thead>

      <tbody>

        @foreach($order->meal_package_items as $i => $mealPackageItem)
        <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
          <td>{{$mealPackageItem->quantity}}</td>
          <td>{{ $mealPackageItem->meal_package->title }}</td>
          <td style="text-align:center">${{number_format($mealPackageItem->price * $mealPackageItem->quantity, 2)}}</td>
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
        <td style="width:67%;padding-top:10px">
          @if ($order->store->settings->notesForCustomer != null)
          <p>{!! nl2br($order->store->settings->notesForCustomer) !!}</p>
          @endif
        </td>
        <td style="width:33%;margin-left:0px;padding-left:0px">
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
            @if ($order->balance > 0)<tr>
            <td style="border:none"><b>Total</b></td>
              <td style="border:none;text-align:right;position:relative;right:20px">{{ $amount }}</td>
            </tr><tr>
            <td style="border:none"><b>Paid</b></td>
              <td style="border:none;text-align:right;position:relative;right:20px">${{number_format($order->amount - $order->balance, 2)}}</td>
            </tr>
            @endif
          </table>
        </td>
      </tr>
      <tfoot>
        @if ($order->balance > 0)
        <tr>
          <th class="full-left-border-radius bold-text" style="border:none;font-size:18px;position:relative;left:30px">
          Amount Due</th>
          <th class="full-right-border-radius bold-text" style="border:none;font-size:18px;text-align:right;position:relative;right:20px">${{number_format($order->balance, 2)}}</th>
        </tr>
        @endif
        @if ($order->balance <= 0)
        <tr>
          <th class="full-left-border-radius bold-text" style="border:none;font-size:18px;position:relative;left:30px"><b>Total Paid</b></th>
          <th class="full-right-border-radius bold-text" style="border:none;font-size:18px;text-align:right;position:relative;right:20px">{{ $amount }}</th>
        </tr>
        @endif
      </tfoot>
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