		@if ($order->pickup === 0)
		<p>You have a delivery scheduled for today, {{ $order->delivery_date->format('D, m/d/Y') }}, from {{ $order->store_name }}</p>
		@elseif ($order->pickup === 1)
		<p>You have a pickup scheduled for today, {{ $order->delivery_date->format('D, m/d/Y') }}, from {{ $order->store_name }}</p>
		<p>Pickup Instructions: {{ $settings->pickupInstructions }}</p>
		@endif
		
		<p>Order #{{ $order->order_number }}</p>
		<p>Subtotal: ${{ number_format($order->preFeePreDiscount, 2) }}</p>
		<p>Meal Plan Discount: ${{ number_format($order->mealPlanDiscount, 2) }}</p>
		<p>Delivery Fee: ${{ number_format($order->deliveryFee, 2) }}</p>
		<p>Processing Fee: ${{ number_format($order->processingFee, 2) }}</p>
		<p>Total: ${{ number_format($order->amount, 2) }}</p>
		<p>Customer Name: {{ $customer->name }}</p>
		<p>Customer Address: {{ $customer->address }}</p>
		<p>Customer City: {{ $customer->city }}</p>
		<p>Customer State: {{ $customer->state }}</p>
		<p>Customer Zip: {{ $customer->zip }}</p>
		<p>Order Placed: {{ $order->created_at->format('D, m/d/Y')}}</p>
		@if ($order->pickup === 0)
		<p>Delivery Instructions: {{ $customer->delivery }}</p>
		@endif



		@foreach($order->meals as $meal)
		  <p>{{ $meal->quantity }} x {{ $meal->title }}</p>
		@endforeach


