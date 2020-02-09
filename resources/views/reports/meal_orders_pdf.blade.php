<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset(mix('/css/print.css')) }}">
</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    @if ($params->productionGroupTitle != null)
    <h1>{{ $params->productionGroupTitle }} - Production</h1>
    @else
    <h1>Production</h1>
    @endif

    <div class="delivery-part">
      @if ($delivery_dates)
        <h1>
          Delivery Days:
          {{ $delivery_dates['from']->format($params->date_format) }} -
          {{ $delivery_dates['to']->format($params->date_format) }}
        </h1>
      @else
        <h1>All Delivery Dates</h1>
      @endif

      <h2>{{ date('m/d/Y h:i:a')}}</h2>
      <div style="clear:both"></div>
    </div>

    <div class="unbreakable">
      <table border="1" width="100" class="light-border">
        <thead>
          <tr>
            <th style="width:150px"><h4>Size</h4></th>
            <th><h4>Item</h4></th>
            @if(!$params['group_by_date'] || $params['group_by_date'] === 'false')
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
