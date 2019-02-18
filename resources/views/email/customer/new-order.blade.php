<!-- <div class="card">
	<div class="card-body"> -->
		Thank you for your order from {{ $order->store_name }}.
		Your meals will be prepped and delivered to you on {{ $order->delivery_date }}
		<!-- <p>Please contact {{ $order->store_name }} for any issues at {{ $order->store->store_detail->phone }}</p> -->
		Order Details
		Subtotal: {{ $order->preFeePreDiscount }}
		Meal Plan Discount: {{ $order->mealPlanDiscount }}
		Delivery Fee: {{ $order->deliveryFee }}
		Processing Fee: {{ $order->processingFee) }}
		Total: {{ $order->amount }}


<!-- 	</div>
</div>
 -->


<!-- {{ $subscription }} -->



<!-- 
<li class="checkout-item">
	<div class="row">
	  <div class="col-md-4">
	    <strong>Meal Plan Discount:</strong>
	  </div>
	  <div class="col-md-3 offset-5">
	  	{{ format.money(mealPlanDiscount) }}
	  </div>
	</div>
</li>
 -->

