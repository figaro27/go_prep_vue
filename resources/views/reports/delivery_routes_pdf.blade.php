<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset('css/print.css') }}">
</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
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
      {{$i + 1}}.<br>
      Order #{{$row['order']->order_number}}<br>
      {{$row['name']}}<br>
      {{$row['address']}}<br>
      {{$row['phone']}}<br>
      Instructions: {{$row['instructions']}}<br>
      <hr>
    @endforeach
    </div>
  </div>
</body>

</html>