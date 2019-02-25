<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset('css/print.css') }}">

</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    <h1>Meals By Customer</h1>
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
      
      <h4>Order #{{$order['order_number']}}</h4>
      <h4>Customer: {{$orderGroup['user']->name }}</h4>
      <h4>Address: {{$order['address']}}, {{$order['city']}}, {{$order['state']}}, {{$order['zip']}}</h4>
      <h5>Delivery Instructions: {{$order['delivery']}}</h5>

        @if(!count($order['meal_quantities']))
          None
        @else
          <table border="1" width="100">
            <tbody>
              @foreach($order['meal_quantities'] as $i => $row)
                <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
                  @foreach($row as $value)
                    <td>{{ $value }}</td>
                  @endforeach
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif

      @endforeach
    @endforeach
  </div>
</body>

</html>