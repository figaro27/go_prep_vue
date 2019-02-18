<div class="card">
	<div class="card-body">
		<h4>Thank you for your order from {{ $order->store_name }}</h4>
		<p>Your meals will be prepped and delivered to you on {{ $order->delivery_date }}</p>
		

		<h5>Order Details</h5>
		<p>Subtotal: ${{ number_format($order->preFeePreDiscount, 2) }}</p>
		<p>Meal Plan Discount: ${{ number_format($order->mealPlanDiscount, 2) }}</p>
		<p>Delivery Fee: ${{ number_format($order->deliveryFee, 2) }}</p>
		<p>Processing Fee: ${{ number_format($order->processingFee, 2) }}</p>
		<p><strong>Total: ${{ number_format($order->amount, 2) }}</strong></p>

		Bag: {{ $bag }}
		<br><br><br>
		Pickup: {{ $pickup }}
		<br><br><br>
		Card: {{ $card }}
		<br><br><br>
		StoreCustomer: {{ $storeCustomer }}
		<br><br><br>
		Customer: {{ $customer }}
		<br><br><br>
		Subscription: {{ $subscription }}
		<br><br><br>


	</div>
</div>


