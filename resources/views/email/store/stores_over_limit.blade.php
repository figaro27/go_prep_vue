@foreach($stores as $i => $store)
<p><b>#{{ $i + 1}}</b></p>
<p><b>Store Name:</b> {{ $store['store_name'] }} </p>
<p><b>Contact Name:</b> {{ $store['contact_name'] }} | <b>Contact Email:</b> {{ $store['contact_email'] }} | <b>Contact Phone:</b> {{ $store['contact_phone'] }}</p>
<p><b>Price:</b> {{ $store['amount'] / 100 }} | <b>Period:</b> {{ $store['period'] }}</p>
<p><b>Plan Name:</b> {{ $store['plan_name'] }} | <b>Allowed Orders:</b> {{ $store['allowed_orders'] }}</p>
@if ($store['plan_notes'])
<p><b>Notes:</b> {{ $store['plan_notes'] }}</p>
@endif
@if ($store['joined_store_ids'])
<p><b>Joined Store IDs:</b> {{ $store['joined_store_ids'] }}</p>
@endif
<p><b>Last Month's Orders:</b> {{ $store['last_month_total_orders'] }}</p>
<p><b>Month's Over Limit:</b> {{ $store['months_over_limit'] }}</p>
<br><br>
@endforeach