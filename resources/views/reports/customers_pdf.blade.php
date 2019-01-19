<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset('css/print.css') }}">
</head>

<body>
  <h2>Customers</h2>
  <table border="1">
    <thead>
      <tr>
        <th>Name</th>
        <th>Phone</th>
        <th>Address</th>
        <th>City</th>
        <th>State</th>
        <th>Customer Since</th>
        <th>Total Orders</th>
        <th>Total Paid</th>
        <th>Last Order</th>
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