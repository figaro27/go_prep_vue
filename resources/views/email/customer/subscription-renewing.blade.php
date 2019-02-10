Hello {{$user->name}},

In less than 24 hours, your Meal Plan will automatically renew.

Subscription: {{ $subscription->id }}

Meals:

@foreach($subscription->meals as $meal)
  {{ $meal->title }} x {{ $meal->quantity }}
@endforeach