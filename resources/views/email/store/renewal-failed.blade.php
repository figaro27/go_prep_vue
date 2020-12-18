<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

</head>

<body class="body">
<p><b>Store ID:</b> {{ $store['id'] }}</p>
<p><b>Sub ID:</b> {{ $id }}</p>
<p><b>Sub Stripe ID:</b> {{ $stripe_id }}</p>
<p><b>Store Name:</b> {{ $store['store_detail']['name'] }}</p>
<p><b>Customer Email:</b> {{ $user['email'] }}</p>
<p><b>User ID:</b> {{ $user['id'] }}</p>
<p><b>Error:</b> {{ $error }}</p>
<p><b>Time:</b> {{ $timestamp }}</p>
</body>

</html>





