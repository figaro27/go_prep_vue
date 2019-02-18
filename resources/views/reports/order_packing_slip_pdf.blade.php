<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset('css/print.css') }}">
  <link rel="stylesheet" href="{{ asset('sass/_custom.scss') }}">
</head>

<body>
  <div class="row">
    <div class="col-8 address">
      <h3>Customer Details</h3>
      <p>
        {{$order->user->name}}
      </p>
      <p>
        {{$order->user->details->address}}<br />
        {{$order->user->details->city}}<br />
        {{$order->user->details->state}}<br />
        {{$order->user->details->zip}}<br />
        {{$order->user->details->country}}<br />
      </p>
      <br>
      <p>
        <strong>Delivery Instructions:</strong><br />
        {{$order->user->details->delivery}}
      </p>
    </div>
    <div class="col-4">
      <h3>Company Details</h3>
      <img src="http://{{$order->store->details->domain}}.dev.goprep.com/{{$order->store->details->logo}}"/>
      <h4>{{$order->store->details->name}}</h4>
      <a href="http://{{$order->store->details->domain}}.goprep.com">{{$order->store->details->domain}}.goprep.com</a>
    </div>
  </div>

  <div class="row mt-5">
      <div class="col-8">
          <h3>Order Details</h3>
          Order #{{$order->order_number}}<br />
          Order Placed: {{$order->created_at->format('D, m/d/Y')}}<br />
          To Be Delivered: {{$order->delivery_date->format('D, m/d/Y')}}<br />
          <strong>Total: ${{number_format($order->amount, 2)}}</strong>
      </div>
  </div>

  <h2>Meals</h2>
  <table border="1">
    <thead>
      <tr>
        <th>Quantity</th>
        <th>Meal Name</th>
        <th>Price</th>
      </tr>
    </thead>

    <tbody>
      @foreach ($order->meals as $i => $meal)
      <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
          <td>{{$meal->quantity || 1}}</td>
          <td>{{$meal->title}}</td>
          <td>${{number_format($meal->price * max($meal->quantity, 1), 2)}}</td>
      </tr>
      @endforeach
    </tbody>
  
  </table>
</body>

</html>