<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset('css/print.css') }}">
</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    <h1>Ingredients</h1>
    <h2>{{ date("m/d/Y") }}</h2>
    <table border="1" width="100">
      <thead>
        <tr>
          <th>Meal</th>
          <th>Ingredient</th>
          <th>Quantity</th>
          <th>Unit</th>

        </tr>
      </thead>

      <tbody>
        @foreach ($data as $i => $row)
        <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
          @foreach($row as $value)
            <td>         
              {{ $value }}        
            </td>
          @endforeach
        </tr>
        @endforeach
      </tbody>
    
    </table>
  </div>
</body>

</html>