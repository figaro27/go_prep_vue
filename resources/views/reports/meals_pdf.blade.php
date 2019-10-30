<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset(mix('/css/print.css')) }}">
</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    <h1>Items</h1>
    <h2>{{ date("m/d/Y") }}</h2>
    <div class="unbreakable">
      <table border="1" width="100">
        <thead>
          <tr>
            <th>Status</th>
            <th>Title</th>
            <th>Categories</th>
            <th>Tags</th>
            <th>Contains</th>
            <th>Lifetime Orders</th>
            <th>Added</th>
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
