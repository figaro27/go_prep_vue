<!--Data Finished-->
		@if ($pickup === 0)
		<p>A new meal plan was created by {{ $customer->name }} with first delivery scheduled for {{ $order->delivery_date->format('D, m/d/Y') }}</p>
		@elseif ($pickup === 1)
		<p>A new meal plan was created by {{ $customer->name }} with first pickup scheduled for {{ $order->delivery_date->format('D, m/d/Y') }}</p>
		@endif

		
		<p>Order #{{ $order->order_number }}</p>
		<p>Subtotal: ${{ number_format($order->preFeePreDiscount, 2) }}</p>
		<p>Meal Plan Discount: ${{ number_format($order->mealPlanDiscount, 2) }}</p>
		<p>Delivery Fee: ${{ number_format($order->deliveryFee, 2) }}</p>
		<p>Processing Fee: ${{ number_format($order->processingFee, 2) }}</p>
		<p>Total: ${{ number_format($order->amount, 2) }}</p>
		<p>Card Last 4: {{ $card->last4 }}</p>
		<p>Customer Name: {{ $customer->name }}</p>
		<p>Customer Address: {{ $customer->address }}</p>
		<p>Customer City: {{ $customer->city }}</p>
		<p>Customer State: {{ $customer->state }}</p>
		<p>Customer Zip: {{ $customer->zip }}</p>
		<p>Order Placed: {{ $order->created_at->format('D, m/d/Y')}}</p>
		@if ($pickup === 0)
		<p>Delivery Instructions: {{ $customer->delivery }}</p>
		@endif


		@foreach($subscription->meals as $meal)
		  <p>{{ $meal->quantity }} x {{ $meal->title }}</p>
		@endforeach


