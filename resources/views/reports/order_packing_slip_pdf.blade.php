<!doctype html>
<html>
@php
$currency = $order->store->settings->currency;
$subtotal = $order->preFeePreDiscount > 0 ? @money($order->preFeePreDiscount, $currency, 2) : 0;
$mealPlanDiscount = $order->mealPlanDiscount > 0 ? @money($order->mealPlanDiscount, $currency, 2) : 0;
$deliveryFee = $order->deliveryFee > 0 ? @money($order->deliveryFee, $currency, 2) : 0;
$gratuity = $order->gratuity > 0 ? @money($order->gratuity, $currency, 2) : 0;
$coolerDeposit = $order->coolerDeposit > 0 ? @money($order->coolerDeposit, $currency, 2) : 0;
$processingFee = $order->processingFee > 0 ? @money($order->processingFee, $currency, 2) : 0;
$salesTax = $order->salesTax > 0 ? @money($order->salesTax, $currency, 2) : 0;
$coupon = $order->couponReduction > 0 ? @money($order->couponReduction, $currency, 2) : 0;
$couponCode = $order->couponCode;
$purchasedGiftCard = $order->purchased_gift_card_code;
$purchasedGiftCardReduction = $order->purchasedGiftCardReduction;
$promotionReduction = $order->promotionReduction;
$pointsReduction = $order->pointsReduction;
$referralReduction = $order->referralReduction;
$amount = $order->amount > 0 ? @money($order->amount, $currency, 2) : 0;
$deposit = $order->deposit > 0 ? @money($order->deposit, $currency, 2) : 0;
$cashOrder = $order->cashOrder;
$balance = $order->balance;
$brandColor = $order->store->settings->color;
$hot = $order->hot;
$deliveryInstructions = $order->user->userDetail->delivery;
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
  @if ($order->balance > 0 || $hot)
  <div class="row">
    @if ($order->balance > 0)
    <h1 class="bold-text red" style="float:right">
    BALANCE DUE
    </h1>
    @endif
    @if ($order->balance > 0 && $hot)
    <h1 class="bold-text red" style="float:right;padding-left:20px;padding-right:20px">
    |
    </h1>
    @endif
    @if ($hot)
    <h1 class="bold-text red" style="float:right">HOT</h1>
    @endif

    
   
  </div>
  @endif
  <div class="row">
    <div class="col-4">
          <p class="text-16 bold-text" style="text-transform: uppercase;color: #3e3e3e;padding-bottom:0px;margin-bottom:0px">{{ $order->store->details->name }}</p>
@if ($squareLogo)
<img src="{{$logo}}" style="width:100px;height:auto;"/>
@else
          <img src="{{$logo}}" style="width:200px;height:auto;"/>
@endif
          <p class="text-11">{{ $order->store->details->address }}</p>
    <p class="text-11">{{ $order->store->details->city }}, {{ $order->store->details->state }}, {{ $order->store->details->zip }}</p>
          <p class="text-11">{{ $order->store->details->phone }}</p>
          @if ($order->store->settings->website) 
          <p class="text-11">{{ $order->store->settings->website }}</p>
          @else 
          <p class="text-11">www{{$order->store->settings->domain}}.goprep.com</p>
          @endif
      </div>
    
    <center>


      <div class="col-4 align-right">
        @if ($order->user->details->companyname)
          <p class="text-16 bold-text" style="text-transform: uppercase;color: #3e3e3e;">{{ $order->user->details->companyname }}</p>
          @endif
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
        <p>Manual Order: {{$order->created_at->format($order->store->settings->date_format)}}</p>
        @else
        <p>Online Order: {{$order->created_at->format($order->store->settings->date_format)}}</p>
        @endif
      </div>
      
      <div class="col-4 right-text">
          @if ($order->dailyOrderNumber && $order->store->modules->dailyOrderNumbers)
          <p class="text-16 bold-text" style="text-transform: uppercase;color: #3e3e3e;font-size:40px">#{{$order->dailyOrderNumber}}</p>
          @else
          <p class="text-16 bold-text" style="text-transform: uppercase;color: #3e3e3e;">Order ID: {{$order->order_number}}</p>
          @endif
          @if ($order->pickup === 0)
          <p class="text-16 bold-text" style="text-transform: uppercase;color: #3e3e3e;font-size:25px">{{$order->transfer_type}}</p>
          @endif
          @if ($order->pickup === 1)
          <p>Pickup</p>
          @endif
          @if ($order->dailyOrderNumber && $order->store->modules->dailyOrderNumbers)
          <p>Order ID: {{$order->order_number}}</p>
          @endif
          @if ($order->staff_id)
          <p>Order Taken By: {{ $order->staff_member }}</p>
          @endif
          @if (!$order->store->modules->hideTransferOptions)
          @if ($order->pickup_location_id)
          <p>Pickup Location: {{ $order->pickup_location_name }}</p>
          @endif
          @if ($order->isMultipleDelivery === 0)
          <p>Date: {{$order->delivery_date->format($order->store->settings->date_format)}}</p>
          @endif
          @if ($order->isMultipleDelivery === 1)
          <p>Dates: {{ $order->multipleDates }}
          @endif
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
          <p>{{ $deliveryInstructions }}</p>
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
    <table class="no-border table-heading" style="border-style:none;">
      <thead>
          <th class="top-left-border-radius drop-shadow no-border" style="text-align:center">Quantity</th>
          <th class="drop-shadow no-border" style="width:100px">Size</th>
          <th class="drop-shadow no-border">Item</th>
          <th class="top-right-border-radius drop-shadow no-border" style="text-align:right;padding-right:12px">Price</th>
      </thead>

      <tbody>

        @foreach($order->meal_package_items as $i => $mealPackageItem)
        <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
          <td style="text-align:center">{{$mealPackageItem->quantity}}</td>
          <td>
            @if ($mealPackageItem->customSize)
            {{ $mealPackageItem->customSize }}
            @else
            {{ isset($mealPackageItem->meal_package_size) && $mealPackageItem->meal_package_size? $mealPackageItem->meal_package_size->title : $mealPackageItem->meal_package->default_size_title }}
            @endif
          </td>
          <td>{{ $mealPackageItem->customTitle ? $mealPackageItem->customTitle : $mealPackageItem->meal_package->title }}</td>
          <td style="text-align:right;padding-right:12px">@money($mealPackageItem->price * $mealPackageItem->quantity, $currency, 2)</td>
        </tr>

        @php
        $count = 0;
        @endphp
        @foreach($order->visible_items as $i => $item)
        @if ($item->meal_package_order_id === $mealPackageItem->id)
        <tr class="{{ $count % 2 === 0 ? 'evenrow' : 'oddrow' }}">
          <td style="text-align:center">{{$item->quantity}}</td>
          <td>{{ $item->base_size }}</td>
          <!--<td>{!! $item->html_title !!}</td>!-->
          <td>{!! $item->base_title !!}</td>
          <td style="text-align:right;padding-right:12px">
            @if ($item->added_price > 0)
              <span style="padding-right:8px">(@money($item->added_price, $currency, 2))</span> In Package
            @else
              In Package
            @endif
          </td>
        </tr>
        @php
        $count += 1;
        @endphp
        @endif
        @endforeach
        @endforeach


        @php
        $count = 0;
        @endphp
        @foreach($order->visible_items as $i => $item)
        <tr class="{{ $count % 2 === 0 ? 'evenrow' : 'oddrow' }}">
        @if ($item->meal_package_order_id === null)
        
          <td style="text-align:center">{{$item->quantity}}</td>
          <td>{{ $item->base_size }}</td>
          <!--<td>{!! $item->html_title !!}</td>!-->
          <td>{!! $item->base_title !!}</td>
          <td style="text-align:right;padding-right:12px">
            @if ($item->attached || $item->free)
            Included
            @else
            @money($item->price, $currency, 2)
            @endif
          </td>
          @php
        $count += 1;
        @endphp
        @endif
        </tr>
        @endforeach

        @php
        $count = 0;
        @endphp
        @if (count($order->lineItemsOrders))
        @foreach ($order->lineItemsOrders as $i => $lineItemOrder)
        <tr class="{{ $count % 2 === 0 ? 'evenrow' : 'oddrow' }}">
          <td style="text-align:center">{{$lineItemOrder->quantity}}</td>
          <td>{!! $lineItemOrder->size !!}</td>
          <td>{!! $lineItemOrder->title !!}</td>
          <td style="text-align:right;padding-right:12px">@money($lineItemOrder->price * $lineItemOrder->quantity, $currency, 2)</td>
        </tr>
        @php
        $count += 1;
        @endphp
        @endforeach
        @endif
      </tbody>
    </table>
    <table class="no-border drop-shadow" style="border-style:none">
      <tr>
        <td style="width:70%;padding:5px 5px 20px 0px">
          @if ($order->publicNotes !== null)
          <p style="position:relative;top:10px;font-weight:bold;" class="text-12">Order Notes</p>
          <p style="position:relative;top:10px;padding-bottom:8px" class="text-12">{!! nl2br($order->publicNotes) !!}</p>
          @endif

          @if ($order->store->settings->notesForCustomer != null)
          <p style="position:relative;top:10px" class="text-11">{!! nl2br($order->store->settings->notesForCustomer) !!}</p>
          @endif
        </td>
        <td style="width:30%;margin-left:0px;padding-left:0px">
          <table border="0" style="border:0px;border-style:none;">
            @if ($order->prepaid)
            <tr>
              <td style="border:none"><b>(Prepaid Subscription Order)</b></td>
            </tr>
            @endif
            <tr>
              <td style="border:none"><b>Subtotal</b></td>
              <td style="border:none;text-align:right;position:relative;right:8px">{{ $subtotal }}</td>
            </tr>
            @if ($order->mealPlanDiscount > 0)<tr>
              <td style="border:none"><b>Subscription Discount</b></td>
              <td style="border:none;text-align:right;position:relative;right:8px">{{ $mealPlanDiscount }}</td>
            </tr>@endif
            @if ($order->salesTax > 0)<tr>
              <td style="border:none"><b>Sales Tax</b></td>
              <td style="border:none;text-align:right;position:relative;right:8px">{{ $salesTax }}</td>
            </tr>@endif
            @if ($order->processingFee > 0)<tr>
              <td style="border:none"><b>Processing Fee</b></td>
              <td style="border:none;text-align:right;position:relative;right:8px">{{ $processingFee }}</td>
            </tr>@endif
            @if ($order->deliveryFee > 0)<tr>
              <td style="border:none"><b>{{ $order->transfer_type }} Fee</b></td>
              <td style="border:none;text-align:right;position:relative;right:8px">{{ $deliveryFee }}</td>
            </tr>@endif
            @if ($order->couponReduction > 0)<tr>
              <td style="border:none"><b>Coupon</b></td>
              <td style="border:none;text-align:right;position:relative;right:8px">@if (!$order->manual)({{ $couponCode }}) @endif {{ $coupon }}</td>
            </tr>@endif
            @if ($order->purchasedGiftCardReduction > 0)<tr>
              <td style="border:none"><b>Coupon</b></td>
              <td style="border:none;text-align:right;position:relative;right:8px">({{ $purchasedGiftCard }}) @money($purchasedGiftCardReduction, $currency, 2)</td>
            </tr>@endif
            @if ($order->referralReduction > 0)<tr>
              <td style="border:none"><b>Referral Discount</b></td>
              <td style="border:none;text-align:right;position:relative;right:8px">@money($referralReduction, $currency, 2)</td>
            </tr>@endif
            @if ($order->promotionReduction > 0)<tr>
              <td style="border:none"><b>Promotional Discount</b></td>
              <td style="border:none;text-align:right;position:relative;right:8px">@money($promotionReduction, $currency, 2)</td>
            </tr>@endif
            @if ($order->pointsReduction > 0)<tr>
              <td style="border:none"><b>Points Discount</b></td>
              <td style="border:none;text-align:right;position:relative;right:8px">@money($pointsReduction, $currency, 2)</td>
            </tr>@endif
            @if ($order->gratuity > 0)<tr>
              <td style="border:none"><b>Gratuity</b></td>
              <td style="border:none;text-align:right;position:relative;right:8px">{{ $gratuity }}</td>
            </tr>@endif
            @if ($order->coolerDeposit > 0)<tr>
              <td style="border:none"><b>Cooler Deposit</b></td>
              <td style="border:none;text-align:right;position:relative;right:8px">{{ $coolerDeposit }}</td>
            </tr>@endif
            <tr>
            <td style="border:none"><b>Total</b></td>
              <td style="border:none;text-align:right;position:relative;right:8px">{{ $amount }}</td>
            </tr><tr>
            <td style="border:none"><b>Paid</b></td>
              <td style="border:none;text-align:right;position:relative;right:8px">@money($order->amount - $order->balance, $currency, 2)</td>
            </tr>
            
            @if ($order->subscription && $order->subscription->prepaid)
              <span style="font-family: 'Open Sans', Arial, sans-serif; font-size:14px; color:#3b3b3b;">Prepaid</span>
            @endif
          </table>
        </td>
      </tr>
      <tfoot>
        
        <tr>
          @if ($order->balance === 0.00 || $order->balance === 0 || $order->balance === null)
            <th class="full-left-border-radius bold-text" style="border:none;font-size:18px;position:relative;left:30px">Total Paid</th>
            <th class="full-right-border-radius bold-text" style="border:none;font-size:18px;text-align:right;position:relative;right:20px">{{ $amount }}</th>
          @endif
          @if ($order->balance > 0)
            <th class="full-left-border-radius bold-text" style="border:none;font-size:18px;position:relative;left:30px">Amount Due</th>
            <th class="full-right-border-radius bold-text" style="border:none;font-size:18px;text-align:right;position:relative;right:20px">@money($order->balance, $currency, 2)</th>
          @endif
          @if ($order->balance < 0)
            <th class="full-left-border-radius bold-text" style="border:none;font-size:18px;position:relative;left:30px">Amount Owed To Customer</th>
            <th class="full-right-border-radius bold-text" style="border:none;font-size:18px;text-align:right;position:relative;right:20px">@money($order->balance * -1, $currency, 2)</th>
          @endif

        </tr> 
      </tfoot>
    </table>


    <br><br>
  @php
  $titles = [];
@endphp
    @foreach ($order->visible_items as $i => $item)
    @if ($item->instructions && !in_array($item->short_title, $titles))
    <p><b>{{ $item->short_title }}</b>: {{ $item->instructions }}</p>
  @php
  array_push($titles, $item->short_title);
  @endphp
    @endif
    @endforeach
  </div>
</div>
</body>


</html>