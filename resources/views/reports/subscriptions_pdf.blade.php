<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset('css/print.css') }}">
</head>

<body>
  <h2>Meal Plans</h2>
  <table border="1">
    <thead>
      <tr>
        <th>Meal Plan #</th>
        <th>Name</th>
        <th>Address</th>
        <th>Zip</th>
        <th>Phone</th>
        <th>Total</th>
        <th>Meal Plan Created</th>
        <th>Delivery Day</th>
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