<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset(mix('/css/print.css')) }}">
</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    @if ($params->productionGroupTitle != null)
    <h1>{{ $params->productionGroupTitle }} - Meal Production</h1>
    @else
    <h1>Meal Production</h1>
    @endif

    <div class="delivery-part">
      @if ($delivery_dates)
        <h2>
          Delivery Days:
          {{ $delivery_dates['from']->format('D, m/d/Y') }} -
          {{ $delivery_dates['to']->format('D, m/d/Y') }}
        </h2>
      @else
        <h2>All Delivery Dates</h2>
      @endif

      <h2>{{ date('m/d/Y h:i:a')}}</h2>
      <div style="clear:both"></div>
    </div>

    <div class="unbreakable">
      <table border="1" width="100" class="light-border">
        <thead>
          <tr>
            <th style="border:none"><h4>Meal</h4></th>
            @if(!$params['group_by_date'] || $params['group_by_date'] === 'false')
            <th style="border:none"><h4>Orders</h4></th>
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
