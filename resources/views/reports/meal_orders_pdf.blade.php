<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset('css/print.css') }}">
</head>

<body>
  <h1>Production</h1>
  <h2>{{ date(DATE_RFC2822) }}</h2>

  <table border="1">
    <thead>
      <tr>
        <th>Title</th>
        <th>Active Orders</th>
        <th>Total Price</th>
      </tr>
    </thead>
    <tbody>
      @foreach($data as $i => $row)
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