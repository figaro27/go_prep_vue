<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset('css/print.css') }}">
</head>

<body>
  <h1>Orders</h1>
  <h2>{{ date("m/d/Y") }}</h2>
  <table border="1">
    <thead>
      <tr>
        <th>Order ID</th>
        <th>Name</th>
        <th>Address</th>
        <th>Zip</th>
        <th>Phone</th>
        <th>Total</th>
        <th>Order Placed</th>
        <th>Delivery Date</th>
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
</body>

</html>