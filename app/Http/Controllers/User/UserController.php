<?php

namespace App\Http\Controllers\User;

use App\Store;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Validator;
use Auth;

class UserController extends Controller
{
    use ValidatesRequests;

    protected $store;
    protected $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (defined('STORE_ID')) {
                $this->store = Store::with(['meals', 'settings'])->find(
                    STORE_ID
                );
            }

            $this->user = auth('api')->user();

            return $next($request);
        });
    }

    public function index()
    {
        return $this->user;
    }

    public function getCustomer()
    {
        return $this->user->customer;
    }

    public function updateEmail(Request $request)
    {
        $validator = Validator::make($request->all(), []);

        $validator->sometimes('email', 'unique:users,email', function ($input) {
            return $input->email !== Auth::user()->email;
        });

        $newEmail = $request->get('email');
        $this->user->email = $newEmail;
        $this->user->update();
    }
}
