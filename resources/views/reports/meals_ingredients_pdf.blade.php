<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset('css/print.css') }}">
</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    <h1>Ingredients</h1>
    <h2>{{ date("m/d/Y") }}</h2>
    <div class="unbreakable">
      <table border="1" width="100">
        <thead>
          <tr>
            <th><h4>Meal</h4></th>
            <th><h4>Ingredient</h4></th>
            <th><h4>Quantity</h4></th>
            <th><h4>Unit</h4></th>

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
  </div>
</body>

</html>