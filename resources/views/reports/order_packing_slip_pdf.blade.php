<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset('css/print.css') }}">
</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    <div class="row">
      <div class="col-8 address">
        <h4>Customer</h4>
        <p>
          {{$order->user->name}}<br>
          {{$order->user->details->address}}<br>
          {{$order->user->details->city}},
          {{$order->user->details->state}}
          {{$order->user->details->zip}}
        </p>
        <h4 class="mt-3">Order Details</h4>
            Order #{{$order->order_number}}<br>
            @if ($order->subscription)
            Meal Plan #{{ $order->subscription->stripe_id }}
            @endif
            Order Placed: {{$order->created_at->format('D, m/d/Y')}}<br>
            To Be Delivered: {{$order->delivery_date->format('D, m/d/Y')}}<br>
            <strong>Total: ${{number_format($order->amount, 2)}}</strong>
      </div>
      <div class="col-4">
        <img src="{{$logo}}" style="zoom: 0.5" />
        <br><br>
        <p><a href="http://{{$order->store->details->domain}}.goprep.com">www.{{$order->store->details->domain}}.goprep.com</a></p>
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
            <td>{{$meal->quantity}}</td>
            <td>{{$meal->title}}</td>
            <td>${{number_format($meal->price * max($meal->quantity, 1), 2)}}</td>
        </tr>
        @endforeach
      </tbody>
    
    </table>
    <br>
    @if ($order->store->settings->notesForCustomer != null)
    <h2>Notes</h2>
    <p>{{$order->store->settings->notesForCustomer}}</p>
    @endif
  </div>
</body>

</html>