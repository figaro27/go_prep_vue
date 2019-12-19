<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset(mix('/css/print.css')) }}">

</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    <h1>Ingredients Production</h1>
    <div class="delivery-part">
      @if ($delivery_dates)
        <h2>
          Delivery Days:
          {{ $delivery_dates['from']->format($params->date_format) }} -
          {{ $delivery_dates['to']->format($params->date_format) }}
        </h2>
      @else
        <h2>All Delivery Dates</h2>
      @endif

      <h2>{{ date('m/d/Y h:i:a')}}</h2>
      <div style="clear:both"></div>
    </div>

    <table border="1" width="100" class="light-border">
      <tbody>
        @foreach($data as $i => $row)
        <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
          @foreach($row as $value)
            <td>{{ $value }}</td>
          @endforeach
        </tr>
        @endforeach
      </tbody>

    </table>
  </div>
</body>

</html>
