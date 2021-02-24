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
        @if ($delivery_dates['from']->format($params->date_format) !== $delivery_dates['to']->format($params->date_format))
        @if (isset($params['byPaymentDate']) && $params['byPaymentDate'] == 'true')
          Payment Dates:
          @else
          Delivery Dates:
          @endif
          {{ $delivery_dates['from']->format($params->date_format) }} - {{ $delivery_dates['to']->format($params->date_format) }}
        @endif
      </h2>

      <h2 style="font-size:15px;position:relative;top:10px">{{ date('m/d/Y h:i:a')}}</h2>
      <div style="clear:both"></div>
    </div>
    <div class="unbreakable">
      <table border="1" width="100" class="light-border payments-report">
        <thead>
          <tr>

            <th style="width:100px">Payment Date</th>


            <th style="width:100px">Delivery Date</th>

            @if (!$params['dailySummary'])
            <th style="width:100px">Order</th>
            <th style="width:100px">Customer</th>
            @endif
            @if ($params['dailySummary'])
            <th>Orders</th>
            @endif
            <th>Subtotal</th>
            @if ($params['couponReduction'])
            <th>(Coupon)</th>
            @if (!$params['dailySummary'])
            <th>(Coupon Code)</th>
            @endif
            @endif
            @if ($params['mealPlanDiscount'])
            <th>(Subscription)</th>
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
            @if ($params['purchasedGiftCardReduction'])
            <th>(Gift Card)</th>
            @endif
            @if ($params['gratuity'])
            <th>Gratuity</th>
            @endif
            @if ($params['coolerDeposit'])
            <th>Cooler Deposit</th>
            @endif
            @if ($params['referralReduction'])
            <th>(Referral)</th> 
            @endif
            @if ($params['promotionReduction'])
            <th>(Promotion)</th> 
            @endif
            @if ($params['pointsReduction'])
            <th>(Points)</th> 
            @endif
            @if ($params['chargedAmount'])
            <th>Additional Charges</th> 
            @endif
            @if ($params['preTransactionFeeAmount'])
            <th>Pre-Fee Total</th> 
            @endif
            @if ($params['transactionFee'])
            <th>(Transaction Fee)</th> 
            @endif
            <th>Total</th>  
            @if ($params['refundedAmount'])
            <th>(Refunded)</th> 
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
            @if (is_numeric($value) && $column !== 'orders' && $column !== 'order_number')
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
