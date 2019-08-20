<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset('css/print.css') }}">
</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    <h1>Meal Production</h1>
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
      <table border="1" width="100">
        <thead>
          <tr>
            <th><h4>Meal</h4></th>
            @if(!$params['group_by_date'])
            <th><h4>Orders</h4></th>
            @else
              @foreach($dates as $i => $date)
                <th>
                  <h4>{{ $date }}</h4>
                </th>
              @endforeach
            @endif
            <!-- <th>Total Price</th> -->
          </tr>
        </thead>
        <tbody>
          @foreach($data as $i => $row)
          <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
            @foreach($row as $value)
              <td>{!! $value !!}</td>
            @endforeach
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>
