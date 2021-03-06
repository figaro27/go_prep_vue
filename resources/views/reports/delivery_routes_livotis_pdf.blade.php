<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset(mix('/css/print.css')) }}">
</head>

<body class="{{ $body_classes }}">
  <div id="print-area" class="unbreakable">
    <h1>Delivery Routes</h1>
     <div class="delivery-part">
      <h2 style="font-size:22px">
        @if ($delivery_dates)
          @if ($delivery_dates['from']->format('D, m/d/Y') === $delivery_dates['to']->format('D, m/d/Y'))
            {{ $delivery_dates['from']->format('D, m/d/Y') }}
          @else
            {{ $delivery_dates['from']->format('D, m/d/Y') }} -{{ $delivery_dates['to']->format('D, m/d/Y') }}
          @endif
        @else
          All Delivery Dates
        @endif
      </h2>


      <!-- <h2>{{ date('m/d/Y h:i:a')}}</h2> -->
      <div style="clear:both"></div>
    </div>

    <div>
    @foreach($data as $i => $row)
      <h5>{{$i + 1}}.</h5>
      @if ($row['order']->dailyOrderNumber)
      <h5>Daily Order #{{$row['order']->dailyOrderNumber}}</h5>
      @endif
      @if ($row['order']->transferTime)
      <h5>Delivery Time: {{$row['order']->transferTime}}</h5>
      @endif
      <h5>Order ID: {{$row['order']->order_number}}</h5>
      <h5>{{$row['name']}}</h5>
      <h5>{{$row['address']}}</h5>
      <h5>{{$row['phone']}}</h5>
      <h5>Instructions: {{$row['instructions']}}</h5>
      <hr align="left" width="50%">
    @endforeach
    </div>
  </div>
</body>

</html>
