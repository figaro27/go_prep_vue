<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset(mix('/css/print.css')) }}">
</head>

<body class="{{ $body_classes }}">
  <div id="print-area" class="unbreakable">
    <h1>Delivery Routes</h1>
    @if ($delivery_dates)
      <h2>
        Delivery Days:
        {{ $delivery_dates['from']->format('D, m/d/Y') }} -
        {{ $delivery_dates['to']->format('D, m/d/Y') }}
      </h2>
    @else
      <h2>All Delivery Dates</h2>
    @endif

    <div>
    @foreach($data as $i => $row)
      <h5>{{$i + 1}}.</h5>
      <h5>Order ID{{$row['order']->order_number}}</h5>
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
