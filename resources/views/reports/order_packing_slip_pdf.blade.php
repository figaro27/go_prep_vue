<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" href="{{ asset('css/print.css') }}">
</head>

<body class="{{ $body_classes }}">
  <div id="print-area">
    <div class="row">
      <div class="col-4 address">
        <h4 class="mt-3">Order Details</h4>
            @if ($params['dailyOrderNumbers'])
            <h2>Order #{{$order['dailyOrderNumber']}}</h2>
            @endif
            <p>Order ID - {{$order->order_number}}</p>
            @if ($order->subscription)
            <p>Subscription #{{ $order->subscription->stripe_id }}</p>
            @endif
            <p>Order Date: {{$order->created_at->format('D, m/d/Y')}}</p>
            @if ($order->pickup === 0)
            <p>Delivery Date: {{$order->delivery_date->format('D, m/d/Y')}} 
              @if ($order->transferTime)
                - {{ $order->transferTime }}
              @endif
            </p>
            @endif
            @if ($order->pickup === 1)
            <p>Pick Up Date: {{$order->delivery_date->format('D, m/d/Y')}}
              @if ($order->transferTime)
                - {{ $order->transferTime }}
              @endif
            </p>
            @endif
            <p><strong>Total: ${{number_format($order->amount, 2)}}</strong></p>
      </div>
      <div class="col-4 address">
        <h4>Customer</h4>
        <p>{{$order->user->name}}</p>
          <p>{{$order->user->details->address}}</p>
          <p>{{$order->user->details->city}},
          {{$order->user->details->state}}
          {{$order->user->details->zip}}</p>
          <p>{{$order->user->details->phone}}</p>
        </p>
      </div>
      <div class="col-4">

            <img src="{{$logo}}" style="zoom: 0.5; max-width: 50%; height: auto;" />

            @if ($order->pickup === 0)
            <h4>DELIVERY</h4>
            @endif
            @if ($order->pickup === 1)
            <h4>PICKUP</h4>
            @endif
         <!-- <p>{{$order->store->details->domain}}.goprep.com</p> -->
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
        @foreach ($order->items as $i => $item)
        <tr class="{{ $i % 2 === 0 ? 'evenrow' : 'oddrow' }}">
            <td>{{$item->quantity}}</td>
            <td>{!! $item->html_title !!}</td>
            <td>${{number_format($item->price, 2)}}</td>
        </tr>
        @endforeach
      </tbody>
    
    </table>
    <br>
    @if ($order->store->settings->notesForCustomer != null)
    <h2>Notes</h2>
    <p>{!! nl2br($order->store->settings->notesForCustomer) !!}</p>
    @endif


    @php
      $mealInstructions = 0
    @endphp

    @foreach ($order->items as $i => $item)
          @if ($item->instructions)
            @php
              $mealInstructions = 1
            @endphp
          @endif
      @endforeach


    @if ($mealInstructions)
      <h2>Special Meal Instructions</h2>
      @foreach ($order->items as $i => $item)
        @if ($item->instructions)
          <p><b>{!! $item->html_title !!}</b> - {{ $item->instructions }}</p>
        @endif
      @endforeach
    @endif

  </div>
</body>
</html>