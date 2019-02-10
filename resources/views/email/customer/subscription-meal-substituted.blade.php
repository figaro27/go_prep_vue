Hello {{ $user->name }},

A meal in your Meal Plan was substituted for another:

Old: {{ $old_meal->title }}<br>
New: {{ $sub_meal->title }}