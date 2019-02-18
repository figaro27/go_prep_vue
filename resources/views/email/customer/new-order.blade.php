<div class="card">
	<div class="card-body">
		<h4>Thank you for your order from {{ $order->store_name }}</h4>
		<p>Your meals will be prepped and delivered to you on {{ $order->delivery_date }}</p>

		<h5>Order Details</h5>
		<p>Subtotal: {{ $order->preFeePreDiscount }}</p>
		<p>Meal Plan Discount: <span class="red"> ({{ $order->mealPlanDiscount }})</span></p>
		<p>Delivery Fee: {{ $order->deliveryFee }}</p>
		<p>Processing Fee: {{ $order->processingFee }}</p>
		<p><strong>Total: {{ $order->amount }}</strong></p>


	</div>
</div>


