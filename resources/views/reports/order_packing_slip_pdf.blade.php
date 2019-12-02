<!doctype html>
<html>
@php
$currency = $order->store->settings->currency_symbol;
$subtotal = $currency . number_format($order->preFeePreDiscount, 2);
$mealPlanDiscount = $currency . number_format($order->mealPlanDiscount, 2);
$deliveryFee = $currency . number_format($order->deliveryFee, 2);
$processingFee = $currency . number_format($order->processingFee, 2);
$salesTax = $currency . number_format($order->salesTax, 2);
$coupon = $currency . number_format($order->couponReduction, 2);
$couponCode = $order->couponCode;
$amount = $currency . number_format($order->amount, 2);
$deposit = $currency . number_format($order->deposit, 2);
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

    .right-text {
      text-align: right;
    }

    .brandColor {
      background: <?= $brandColor ?>;;
      background-color: <?= $brandColor ?>;;
    }

  </style>
</head>


<body class="{{ $body_classes }}">
  <div id="print-area">
  @if ($order->voided)
  <h1 class="center-text bold-text red">VOIDED</h1>
  @endif
  @if ($order->balance > 0)
  <div class="row">
    <h1 class="bold-text red" style="float:right">BALANCE DUE</h1>
  </div>
  @endif
  <div class="row">
    <div class="col-4" style="position:relative;top:40px">
        <p class="text-16 bold-text" style="text-transform: uppercase;color: #3e3e3e;">{{$order->user->name}}</p>
        @if ($order->user->details->address !== 'N/A')
        <p>{{$order->user->details->address}}</p>
        <p>{{$order->user->details->city}},
          {{$order->user->details->state}}
          {{$order->user->details->zip}}</p>
        @endif
        <p>{{$order->user->details->phone}}</p>
        @if (strpos($order->user->email, 'noemail') === false)
        <p>{{$order->user->email}}</p>
        @endif
        @if ($order->manual)
        <p>Manual Order: {{$order->created_at->format('D, m/d/Y')}}</p>
        @else
        <p>Order: {{$order->created_at->format('D, m/d/Y')}}</p>
        @endif
      </div>
    
    <center>
      <div class="col-4 center-text">
          <h4 class="center-text bold-text" style="text-transform: uppercase;color: #3e3e3e;padding-bottom:0px;margin-bottom:0px">{{ $order->store->details->name }}</h4>
          <img src="{{$logo}}" style="width:200px;height:auto"/>
          <p class="center-text text-11">{{ $order->store->details->address }}, {{ $order->store->details->city }}, {{ $order->store->details->state }}, {{ $order->store->details->zip }}</p>
          <p class="center-text text-11">{{ $order->store->details->phone }}</p>
          @if ($order->store->settings->website) 
          <p class="center-text text-11">{{ $order->store->settings->website }}</p>
          @else 
          <p class="center-text text-11">www{{$order->store->settings->domain}}.goprep.com</p>
          @endif
      </div>
      
      <div class="col-4 right-text" style="position:relative;top:40px">
          @if ($order->dailyOrderNumber && $order->store->modules->dailyOrderNumbers)
          <p class="text-16 bold-text" style="text-transform: uppercase;color: #3e3e3e;">Daily Order #{{$order->dailyOrderNumber}}</p>
          <p>Order ID: {{$order->order_number}}</p>
          @else
          <p class="text-16 bold-text" style="text-transform: uppercase;color: #3e3e3e;">Order ID: {{$order->order_number}}</p>
          @endif
          @if ($order->pickup === 0)
          <p>Delivery</p>
          @endif
          @if ($order->pickup === 1)
          <p>Pickup</p>
          @endif
          @if (!$order->store->modules->hideTransferOptions)
          @if ($order->transferTime)
          @if ($order->pickup === 0)
          <p>Delivery Time: {{ $order->transferTime }}</p>
          @endif
          @if ($order->pickup === 1)
          <p>Pickup Time: {{ $order->transferTime }}</p>
          @endif
          @endif
          <p>Date: {{$order->delivery_date->format('D, m/d/Y')}}
          </p>
          @endif
      </div>
    </center>
    </div>
    <br><br>

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
    <table class="no-border table-heading unbreakable" style="border-style:none;">
      <thead>
          <th class="top-left-border-radius drop-shadow no-border" style="text-align:center">Quantity</th>
          <th class="drop-shadow no-border">Size</th>
          <th class="drop-shadow no-border">Item</th>
          <th class="top-right-border-radius drop-shadow no-border" style="text-align:center">Price</th>
      </thead>

      <tbody>

        @foreach($order->meal_package_items as $i => $mealPackageItem)
        <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
          <td style="text-align:center">{{$mealPackageItem->quantity}}</td>
          <td>{{ isset($mealPackageItem->meal_package_size) && $mealPackageItem->meal_package_size? $mealPackageItem->meal_package_size->title:$mealPackageItem->meal_package->default_size_title }}</td>
          <td>{{ $mealPackageItem->meal_package->title }}</td>
          <td style="text-align:center">{{$currency}}{{number_format($mealPackageItem->price * $mealPackageItem->quantity, 2)}}</td>
        </tr>

        @foreach($order->items as $i => $item)
        @if ($item->meal_package_order_id === $mealPackageItem->id)
        <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
          <td style="text-align:center">{{$item->quantity}}</td>
          <td>{{ $item->base_size }}</td>
          <!--<td>{!! $item->html_title !!}</td>!-->
          <td>{!! $item->base_title !!}</td>
          <td style="text-align:center">
            In Package
          </td>
        </tr>

        @endif
        @endforeach

        @endforeach
        @foreach($order->items as $i => $item)
        @if ($item->meal_package_order_id === null)
        <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
          <td style="text-align:center">{{$item->quantity}}</td>
          <td>{{ $item->base_size }}</td>
          <!--<td>{!! $item->html_title !!}</td>!-->
          <td>{!! $item->base_title !!}</td>
          <td style="text-align:center">
            @if ($item->attached || $item->free)
            Included
            @else
            {{$currency}}{{ number_format($item->price, 2) }}
            @endif
          </td>
        </tr>

        @endif
        @endforeach

        @if (count($order->lineItemsOrders))
        @foreach ($order->lineItemsOrders as $i => $lineItemOrder)
        <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
          <td style="text-align:center">{{$lineItemOrder->quantity}}</td>
          <td></td>
          <td>{!! $lineItemOrder->title !!}</td>
          <td style="text-align:center">{{$currency}}{{number_format($lineItemOrder->price * $lineItemOrder->quantity, 2)}}</td>
        </tr>
        @endforeach
        @endif
      </tbody>
    </table>
    <table class="no-border drop-shadow" style="border-style:none">
      <tr>
        <td style="width:70%;padding:5px 5px 20px 0px">
          @if ($order->store->settings->notesForCustomer != null)
          <p style="position:relative;top:10px" class="text-11">{!! nl2br($order->store->settings->notesForCustomer) !!}</p>
          @endif
        </td>
        <td style="width:30%;margin-left:0px;padding-left:0px">
          <table border="0" style="border:0px;border-style:none;">
            <tr>
              <td style="border:none"><b>Subtotal</b></td>
              <td style="border:none;text-align:right;position:relative;right:30px">{{ $subtotal }}</td>
            </tr>
            @if ($order->mealPlanDiscount > 0)<tr>
              <td style="border:none"><b>Subscription Discount</b></td>
              <td style="border:none;text-align:right;position:relative;right:30px">{{ $mealPlanDiscount }}</td>
            </tr>@endif
            @if ($order->salesTax > 0)<tr>
              <td style="border:none"><b>Sales Tax</b></td>
              <td style="border:none;text-align:right;position:relative;right:30px">{{ $salesTax }}</td>
            </tr>@endif
            @if ($order->processingFee > 0)<tr>
              <td style="border:none"><b>Processing Fee</b></td>
              <td style="border:none;text-align:right;position:relative;right:30px">{{ $processingFee }}</td>
            </tr>@endif
            @if ($order->deliveryFee > 0)<tr>
              <td style="border:none"><b>Delivery Fee</b></td>
              <td style="border:none;text-align:right;position:relative;right:30px">{{ $deliveryFee }}</td>
            </tr>@endif
            @if ($order->couponReduction > 0)<tr>
              <td style="border:none"><b>Coupon</b></td>
              <td style="border:none;text-align:right;position:relative;right:30px">({{ $couponCode }}) {{ $coupon }}</td>
            </tr>@endif
            @if ($order->balance > 0)<tr>
            <td style="border:none"><b>Total</b></td>
              <td style="border:none;text-align:right;position:relative;right:30px">{{ $amount }}</td>
            </tr><tr>
            <td style="border:none"><b>Paid</b></td>
              <td style="border:none;text-align:right;position:relative;right:30px">{{$currency}}{{number_format($order->amount - $order->balance, 2)}}</td>
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
          <th class="full-right-border-radius bold-text" style="border:none;font-size:18px;text-align:right;position:relative;right:20px">{{$currency}}{{number_format($order->balance, 2)}}</th>
        </tr>
        @endif
        @if ($order->balance <= 0)
        <tr>
          <th class="full-left-border-radius bold-text" style="border:none;font-size:18px;position:relative;left:30px">Total Paid</th>
          <th class="full-right-border-radius bold-text" style="border:none;font-size:18px;text-align:right;position:relative;right:20px">{{ $amount }}</th>
        </tr>
        @endif
      </tfoot>
    </table>


    <br><br>
    @foreach ($order->items as $i => $item)
    @if ($item->instructions)
    <p><b>{!! $item->title !!}</b>: {{ $item->instructions }}</p>
    @endif
    @endforeach
  </div>
</div>
</body>

</html>