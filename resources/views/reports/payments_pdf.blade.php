<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset('css/print.css') }}">

  <style>
  table tbody tr:first-child {
    font-weight: bold;
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
            <th>Subtotal</th>
            <th>Coupon</th>
            <th>Coupon Reduction</th>
            <th>Meal Plan Discount</th>
            <th>Processing Fee</th>
            <th>Delivery Fee</th>
            <th>Sales Tax</th>
            <th>PreFee Total</th>
            <th>GoPrep Fee</th>
            <th>Stripe Fee</th>
            <th>Total</th>
            <th>Balance</th>
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