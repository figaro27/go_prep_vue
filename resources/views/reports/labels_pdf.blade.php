<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset(mix('/css/print.css')) }}">
</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    <h1>Labels</h1>

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

    <div class="unbreakable">
      <table border="0" width="100">
        <tbody>
          @foreach($data as $i => $row)
          <tr>
            @foreach($row as $value)
              <td>
                <h1>
                  {!! $value->html_title !!}
                  </h1><p>
                  {!! $value->meal->description !!}
                  </p><p>
                  {!! $value->meal->instructions !!}
                  </p><p>
                  {!! $value->store->details->name !!}
                  </p>
                  <h1>
                    @if ($params['labelsNutrition'] === 'none')
                    No Nutrition
                    @elseif ($params['labelsNutrition'] === 'macros')
                    Show Macros
                    @else
                    Show Full Nutrition
                    @endif
                  </h1>
              </td>
            @endforeach
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>
