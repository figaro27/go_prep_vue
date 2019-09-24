<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset('css/print.css') }}">

</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    <h1>Orders Summary</h1>
    @if ($delivery_dates)
      <h2>
        Delivery Days: 
        {{ $delivery_dates['from']->format('D, m/d/Y') }} -
        {{ $delivery_dates['to']->format('D, m/d/Y') }}
      </h2>
    @else
      <h2>All Delivery Dates</h2>
    @endif

    @foreach($data as $x => $orderGroup)
      @if($x > 0)
        <div style="height:30px"></div>
      @endif
      
      @foreach($orderGroup['orders'] as $order)
      <div class="unbreakable">
        @if ($params['dailyOrderNumbers'])
        <h2>Daily Order #{{$order['dailyOrderNumber']}}</h2>
        @endif
        <h5>Order ID - {{$order['order_number']}}</h5>
        <h5>Customer: {{$orderGroup['user']->name }}</h5>
        <h5>Address: {{$order['address']}}, {{$order['city']}}, {{$order['state']}}, {{$order['zip']}}</h5>
        @if ($order['pickup'] === 0)
        <h5>Delivery Instructions: {{$order['delivery']}}</h5>
        @endif
        @if ($order['pickup_location_id'] != null)
        <h5>Pickup Location: 
          {{ $order['pickup_location']->name }}<br>
          {{ $order['pickup_location']->address }},
          {{ $order['pickup_location']->city }},
          {{ $order['pickup_location']->state }}
          {{ $order['pickup_location']->zip }}
        </h5>
        @endif

          @if(!count($order['meal_quantities']))
            None
          @else
            <table border="1" width="100">
              <tbody>
                @foreach($order['meal_quantities'] as $i => $row)
                  <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
                    @foreach($row as $value)
                      <td width="50%">{!! $value !!}</td>
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
                      <td width="50%">{!! $value !!}</td>
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