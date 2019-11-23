<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset(mix('/css/print.css')) }}">

  <style>
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
      @if ($delivery_dates)
        <h2>
          Order Dates:
          {{ $delivery_dates['from']->format('D, m/d/Y') }} -
          {{ $delivery_dates['to']->format('D, m/d/Y') }}
        </h2>
      @else
        <h2>All Order Dates</h2>
      @endif

      <h2>{{ date('m/d/Y h:i:a')}}</h2>
      <div style="clear:both"></div>
    </div>
    <div class="unbreakable">
      <table border="1" width="100" class="light-border payments-report">
        <thead>
          <tr>
            <th style="width:200px">Order Date</th>
            @if ($params['dailySummary'])
            <th>Orders</th>
            @endif
            <th>Subtotal</th>
            @if ($params['dailySummary'] == 0)
            <th>Coupon Code</th>
            @endif
            <th>Coupon Reduction</th>
            <th>Subscription Discount</th>
            <th>Delivery Fee</th>
            <th>Processing Fee</th>
            <th>Sales Tax</th>
            <!-- <th>GoPrep Fee</th>
            <th>Stripe Fee</th> -->
            <th>Total</th>           
            <th>Balance</th>
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
