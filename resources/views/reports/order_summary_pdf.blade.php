<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset(mix('/css/print.css')) }}">

</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    <h1>Orders Summary</h1>
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
      </h2>

      <h2>{{ date('m/d/Y h:i:a')}}</h2>
      <div style="clear:both"></div>
    </div>

    @foreach($data as $x => $orderGroup)
      @if($x > 0)
        <div style="height:30px"></div>
      @endif

      @foreach($orderGroup['orders'] as $order)
      <div class="unbreakable">
        @if ($params['dailyOrderNumbers'])
        <h4>Daily Order #{{$order['dailyOrderNumber']}}</h4>
        @endif
        <h5>
          @if ($order['pickup'] === 0)





@if ($order['isMultipleDelivery'] === 0)
          <span style="font-weight:bold">DELIVERY:</span> {{ $order['delivery_date']->format($params->date_format) }}
            @endif
          @if ($order['isMultipleDelivery'] === 1)
         <span style="font-weight:bold">DELIVERY:</span> {{ $order['multipleDates'] }}
            @endif



          @endif
          @if ($order['pickup'] === 1)
            @if ($order['isMultipleDelivery'] === 0)
          <span style="font-weight:bold">PICKUP:</span> {{ $order['delivery_date']->format($params->date_format) }}
            @endif
          @if ($order['isMultipleDelivery'] === 1)
         <span style="font-weight:bold">PICKUP:</span> {{ $order['multipleDates'] }}
            @endif
          @endif
          @if ($order['transferTime'])
            {{ $order['transferTime'] }}
          @endif
        </h5>
        <h5><span style="font-weight:bold">Order ID:</span> {{$order['order_number']}}</h5>
        <h5><span style="font-weight:bold">Customer:</span> {{$orderGroup['user']->name }}</h5>
        <h5><span style="font-weight:bold">Email:</span> {{$orderGroup['user']->email }}</h5>
        @if ($order['address'] !== 'N/A')
        <h5><span style="font-weight:bold">Address:</span> {{$order['address']}}, {{$order['city']}}, {{$order['state']}}, {{$order['zip']}}</h5>
        @endif
        <h5><span style="font-weight:bold">Phone:</span> {{$order['phone']}}</h5>
        @if ($order['pickup'] === 0)
        @if ($order['delivery'])
        <h5><span style="font-weight:bold">Delivery Instructions:</span> {{$order['delivery']}}</h5>
        @endif
        @endif
        @if ($order['pickup_location_id'] != null)
        <h5><span style="font-weight:bold">Pickup Location:</span>
          {{ $order['pickup_location']->name }}<br>
          {{ $order['pickup_location']->address }},
          {{ $order['pickup_location']->city }},
          {{ $order['pickup_location']->state }}
          {{ $order['pickup_location']->zip }}
        </h5>
        @endif

        @if ($order['notes'] != null)
        <h5><span style="font-weight:bold">Order Notes:</span> {{ $order['notes'] }}</h5>
        @endif

          @if(!count($order['meal_quantities']))
            None
          @else
            <table border="1" width="100" class="light-border">
              <tbody>
                @foreach($order['meal_quantities'] as $i => $row)
                  <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
                    @foreach($row as $value)
                      <td>{!! $value !!}</td>
                    @endforeach
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
            <div style="height:10px"></div>
          @if(count($order['lineItemsOrders']) > 1)
            <table border="1" width="100">
              <tbody>
                @foreach($order['lineItemsOrders'] as $i => $row)
                  <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
                    @foreach($row as $value)
                      <td>{!! $value !!}</td>
                    @endforeach
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif

      </div>

      @endforeach
    @endforeach
  </div>
</body>

</html>
