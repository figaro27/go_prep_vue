<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset(mix('/css/print.css')) }}">
</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    @if ($params->productionGroupTitle != null)
    <h1>Production <span style="font-size:24px">({{ $params->productionGroupTitle }})</span></h1>
    @else
    <h1>Production</h1>
    @endif

    <div class="delivery-part">
      <h2 style="font-size:22px">
        @if ($delivery_dates)
          @if ($delivery_dates['from']->format($params->date_format) === $delivery_dates['to']->format($params->date_format))
            {{ $delivery_dates['from']->format($params->date_format) }}
          @else
            {{ $delivery_dates['from']->format($params->date_format) }} -{{ $delivery_dates['to']->format($params->date_format) }}
          @endif
        @else
          All Delivery Dates
        @endif
        @if ($params->startTime || $params->endTime)
        @if ($params->startTime)
        {{ $params->startTime }}
        @else
        12:00 AM
        @endif
        -
        @if ($params->endTime)
        {{ $params->endTime }}
        @else
        11:59 PM
        @endif
        @endIf
      </h2>


      <h2 style="font-size:15px;position:relative;top:10px">{{ date('m/d/Y h:i:a')}}</h2>
      <div style="clear:both"></div>
    </div>

    <div class="unbreakable">
      <table border="1" width="100" class="light-border">
        <thead>
          <tr>
            @if(!$params['group_by_date'] || $params['group_by_date'] === 'false')
            <th><h4>#</h4></th>
            @else
              @foreach($dates as $i => $date)
                <th>
                  <h4>{{ $date }}</h4>
                </th>
              @endforeach
            @endif
            @if($params['show_time_breakdown'] === 'true')
            <th><h4>Times</h4></th>
            @endif
            <th style="width:150px"><h4>Size</h4></th>
            <th><h4>Item</h4></th>

            @if($params['show_daily_order_numbers'])
            <th><h4>Daily Order #</h4></th>
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

