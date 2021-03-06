<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset(mix('/css/print.css')) }}">
</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    <h1>Leads</h1>
    <h2>{{ date("m/d/Y") }}</h2>
    <div class="unbreakable">
      <table border="1" width="100" class="customers-report">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Referred Customers</th>
            <th>Referred Orders</th>
            <th>Total Revenue</th>
            <th>Referral URL</th>
            <th>Redeem Code</th>
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
