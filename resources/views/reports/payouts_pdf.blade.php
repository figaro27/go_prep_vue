<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset(mix('/css/print.css')) }}">

  <style>
    .text-green {
      color:#006400 !important;
    }
  table tr td:nth-child(3) {
    width: 100px;
    word-break: break-all;
    }


  </style>
</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    <h1>Payouts</h1>
    <div class="delivery-part">
      <h2 style="font-size:22px">
      @if (isset($params['start_date']))
        Dates: 
        @if (!isset($params['end_date']))
          {{ $params['start_date'] }}
        @else
          {{ $params['start_date'] }} - {{ $params['end_date'] }}
        @endif
      @else
        All Dates
      @endif
      </h2>
      <h2 style="font-size:15px;position:relative;top:10px">{{ date('m/d/Y h:i:a')}}</h2>
      <div style="clear:both"></div>
    </div>
    <div class="unbreakable">
      <table border="1" width="100" class="light-border payments-report">
        <thead>
          <tr>
            <th>Initiated</th>
            <th>Arrival Date</th>
            <th>Bank Name</th>
            <th>Total</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($data as $i => $row)
          <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
            @foreach($row as $value)
              <td>{{ $value }}</td>
            @endforeach
          </tr>
          @endforeach
        </tbody>

      </table>
    </div>
  </div>
</body>

</html>
