<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset(mix('/css/print.css')) }}">
</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    <h1>Orders</h1>
    @if ($delivery_dates)
      <h2>
        Delivery Days:
        {{ $delivery_dates['from']->format('D, m/d/Y') }} -
        {{ $delivery_dates['to']->format('D, m/d/Y') }}
      </h2>
    @else
      <h2>All Delivery Dates</h2>
    @endif
    <div class="unbreakable">
      <table border="1" width="100" class="orders-report">
        <thead>
          <tr>
            <th style="width:80px">Daily Order #</th>
            <th>Order ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Address</th>
            <th>Zip</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Total</th>
            <th>Balance</th>
            <th style="width:100px">Order Placed</th>
            <th style="width:100px">Delivery Date</th>
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
