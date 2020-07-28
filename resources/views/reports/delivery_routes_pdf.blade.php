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


      <h2>{{ date('m/d/Y h:i:a')}}</h2>
      <div style="clear:both"></div>
    </div>

    <div>
    <p class="text-18"><strong>Starting Point:</strong> {{ $data[0]['startingAddress']}}</p>
    <p class="text-18"><strong>Total Stops:</strong> {{ $data[0]['stops']}}</p>
    <p class="text-18"><strong>Total Distance:</strong> {{ $data[0]['miles']}} Miles</p>
    <br><br>
    @foreach($data as $i => $row)
    @if ($i > 0)
    <p class="text-16 strong">#{{$i}}) {{ $row['name']}} - {{ $row['address']}}</p>
    <p class="text-16">{{ $row['delivery'] }}</p>
    <br>
    @endif
    @endforeach
    </div>
  </div>
</body>

</html>

