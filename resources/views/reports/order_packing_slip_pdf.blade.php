<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset('css/print.css') }}">
</head>

<body>
  <div class="row">
    <div class="col-8 address">
      <h3>Receipient Details</h3>
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
      <p>
        <strong>Delivery instructions:</strong><br />
        {{$order->user->details->delivery}}
      </p>
    </div>
    <div class="col-4">
      <h3>Store Details</h3>
      <img src="https://picsum.photos/100/60" />
      <h4>{{$order->store->details->name}}</h4>
      <a href="http://{{$order->store->details->domain}}.goprep.com">{{$order->store->details->domain}}.goprep.com</a>
    </div>
  </div>

  <div class="row">
      <div class="col-8">
          <h3>Order Details</h3>
          Order reference: {{$order->order_number}}<br />
          Order placed: {{$order->created_at}}<br />
          To be delivered: {{$order->delivery_date}}<br />
          Total: {{number_format($order->amount, 2)}}
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