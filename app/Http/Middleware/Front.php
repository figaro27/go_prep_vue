<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Handles all non-AJAX requests
 */
class Front
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle($request, Closure $next)
  {
    

  }

}
