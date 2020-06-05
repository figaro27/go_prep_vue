<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset(mix('/css/print.css')) }}">
</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    <h1>Orders</h1>
    <h2 style="font-size:22px">
        @if ($delivery_dates)
          @if ($delivery_dates['from']->format('D, m/d/Y') === $delivery_dates['to']->format('D, m/d/Y'))
            {{ $delivery_dates['from']->format('D, m/d/Y') }}
          @else
            {{ $delivery_dates['from']->format('D, m/d/Y') }} -{{ $delivery_dates['to']->format('D, m/d/Y') }}
          @endif
        @else
          All Delivery Dates
        @endif
      </h2>
    <div class="unbreakable">
      <table border="1" width="100" class="orders-report">
        <thead>
          <tr>
            <th style="width:75px">Daily Order #</th>
            <th>Order ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Address</th>
            <th>Zip</th>
            <th>Phone</th>
            <th style="width:100px">Email</th>
            <th>Total</th>
            <th>Balance</th>
            <th style="width:100px">Order Placed</th>
            <th style="width:100px">Delivery Date</th>
            <th>Time</th>
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
