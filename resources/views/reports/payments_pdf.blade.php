<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset(mix('/css/print.css')) }}">

  <style>
    .text-green {
      color:#006400 !important;
    }
  table tbody tr:first-child {
    font-weight: bold;
  }
  table tr td:nth-child(3) {
    width: 100px;
    word-break: break-all;
    }


  </style>
</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    <h1>Payments</h1>
    <div class="delivery-part">
      <h2 style="font-size:22px">
        @if (isset($params['byOrderDate']) && $params['byOrderDate'])
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
            <th style="width:100px">Order Date</th>
            <th style="width:100px">Delivery Date</th>
            @if ($params['dailySummary'])
            <th>Orders</th>
            @endif
            <th>Subtotal</th>
            @if (!$params['removeCoupon'])
            <th class="text-green">(Coupon)</th>
            @endif
            @if (!$params['removeSubscription'])
            <th class="text-green">(Subscription)</th>
            @endif
            @if (!$params['removeSalesTax'])
            <th>Sales Tax</th>
            @endif
            @if (!$params['removeProcessingFee'])
            <th>Processing Fee</th>
            @endif
            @if (!$params['removeDeliveryFee'])
            <th>Delivery Fee</th>
            @endif
            @if (!$params['removeGiftCard'])
            <th class="text-green">(Gift Card)</th>
            @endif
            @if (!$params['removeReferral'])
            <th class="text-green">(Referral)</th> 
            @endif
            @if (!$params['removePromotion'])
            <th class="text-green">(Promotion)</th> 
            @endif
            @if (!$params['removePoints'])
            <th class="text-green">(Points)</th> 
            @endif
            <th>Total</th>  
            @if (!$params['removeBalance']) 
            <th>Balance</th>
            @endif
            <!-- <th>Refunded</th> -->
          </tr>
        </thead>
        <tbody>
          @foreach ($data as $i => $row)
          <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
            @foreach($row as $value)
              <td>{{ $value }}</td>
            @endforeach
          </tr>
          @endforeach
        </tbody>

      </table>
    </div>
  </div>
</body>

</html>
