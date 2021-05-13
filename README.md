# goprep

1. In order to make all `php artisan xxx` commands work before migration, we need to comment out `__construct` and `handle` function of `app/Console/Commands/Hourly.php` like this.

```
public function __construct()
{
  parent::__construct();
  return; // add this line
  ...
}

public function handle()
{
  return; // add this line
  ...
}
```

2. Do db migration via `php artisan migrate`.

3. Recover `Hourly.php` as it was
