<p>Hello {{$customer->name}},</p>

<p>In less than 24 hours, your Meal Plan will automatically renew.</p>

<p>Subscription: {{ $subscription->stripe_id }}</p>

<p>Meals:</p>

@foreach($subscription->meals as $meal)
  <p>{{ $meal->title }} x {{ $meal->quantity }}</p>
@endforeach