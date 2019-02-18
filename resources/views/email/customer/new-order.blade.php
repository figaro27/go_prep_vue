<!-- <div class="card">
	<div class="card-body"> -->
		{{ $order->store_name }}
		{{ $order->delivery_date }}
		<!-- <p>Please contact {{ $order->store_name }} for any issues at {{ $order->store->store_detail->phone }}</p> -->

		{{ $order->preFeePreDiscount }}
		{{ $order->mealPlanDiscount }}
		{{ $order->deliveryFee }}
		{{ $order->processingFee }}
		{{ $order->amount }}


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

