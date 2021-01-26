<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset(mix('/css/print.css')) }}">

  <style>
    .text-green {
      color:#006400 !important;
    }
  table tr td:nth-child(3) {
    width: 100px;
    word-break: break-all;
    }
  </style>
@php
$currency = $params->currency
@endphp
</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    <h1>Payments @if ($params['dailySummary'] == 'true') (Daily Summary) @endif</h1>
    <div class="delivery-part">
      <h2 style="font-size:22px">
        @if (isset($params['byOrderDate']) && $params['byOrderDate'] == 'true')
          Order Dates:
          @else
          Delivery Dates:
          @endif
        @if ($delivery_dates)
          @if ($delivery_dates['from']->format($params->date_format) === $delivery_dates['to']->format($params->date_format))
            {{ $delivery_dates['from']->format($params->date_format) }}
          @else
            {{ $delivery_dates['from']->format($params->date_format) }} -{{ $delivery_dates['to']->format($params->date_format) }}
          @endif
        @else
          All Delivery Dates
        @endif
      </h2>

      <h2 style="font-size:15px;position:relative;top:10px">{{ date('m/d/Y h:i:a')}}</h2>
      <div style="clear:both"></div>
    </div>
    <div class="unbreakable">
      <table border="1" width="100" class="light-border payments-report">
        <thead>
          <tr>
            @if ($params['created_at'])
            <th style="width:100px">Order Date</th>
            @endif
            @if ($params['delivery_date'])
            <th style="width:100px">Delivery Date</th>
            @endif
            @if ($params['dailySummary'])
            <th>Orders</th>
            @endif
            <th>Subtotal</th>
            @if ($params['couponReduction'])
            <th class="text-green">(Coupon)</th>
            @endif
            @if ($params['mealPlanDiscount'])
            <th class="text-green">(Subscription)</th>
            @endif
            @if ($params['salesTax'])
            <th>Sales Tax</th>
            @endif
            @if ($params['processingFee'])
            <th>Processing Fee</th>
            @endif
            @if ($params['deliveryFee'])
            <th>Delivery Fee</th>
            @endif
            @if ($params['gratuity'])
            <th>Gratuity</th>
            @endif
            @if ($params['coolerDeposit'])
            <th>Cooler Deposit</th>
            @endif
            @if ($params['purchasedGiftCardReduction'])
            <th class="text-green">(Gift Card)</th>
            @endif
            @if ($params['referralReduction'])
            <th class="text-green">(Referral)</th> 
            @endif
            @if ($params['promotionReduction'])
            <th class="text-green">(Promotion)</th> 
            @endif
            @if ($params['pointsReduction'])
            <th class="text-green">(Points)</th> 
            @endif
            <th>Total</th>  
            @if ($params['refundedAmount'])
            <th class="text-green">(Refunded)</th> 
            @endif
            @if ($params['balance']) 
            <th>Balance</th>
            @endif
            <!-- <th>Refunded</th> -->
          </tr>
        </thead>
        <tbody>
          @foreach ($data as $i => $row)
          <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
            @foreach($row as $column => $value)
            @if (is_numeric($value) && $column !== 'orders')
              <td>@money($value, $currency, 2)</td>
            @else
              <td>{{ $value }}</td>
            @endif
            @endforeach
          </tr>
          @endforeach
        </tbody>

      </table>
    </div>
  </div>
</body>

</html>
