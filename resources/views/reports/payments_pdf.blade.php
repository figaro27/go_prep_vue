<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset('css/print.css') }}">

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
    @if ($delivery_dates)
      <h2>
        Order Dates: 
        {{ $delivery_dates['from']->format('D, m/d/Y') }} -
        {{ $delivery_dates['to']->format('D, m/d/Y') }}
      </h2>
    @else
      <h2>All Order Dates</h2>
    @endif
    <div class="unbreakable">
      <table border="1" width="100">
        <thead>
          <tr>
            <th>Payment Date</th>
            @if ($params['dailySummary'])
            <th>Orders</th>
            @endif
            <th>Subtotal</th>
            @if ($params['dailySummary'] == 0)
            <th>Coupon</th>
            @endif
            <th>Coupon Reduction</th>
            <th>Subscription Discount</th>
            <th>Delivery Fee</th>
            <th>Processing Fee</th>
            <th>Sales Tax</th>
            <th>GoPrep Fee</th>
            <th>Stripe Fee</th>
            <th>Total</th>
            @if ($params['dailySummary'] == 0)
            <th>Balance</th>
            @endif
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