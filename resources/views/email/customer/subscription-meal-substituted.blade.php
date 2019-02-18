<!--Data Finished-->
Hello {{ $user->name }},

<p>A meal in your Meal Plan was substituted for another:</p>

<p>Old: {{ $old_meal->title }}</p>
<p>New: {{ $sub_meal->title }}</p>