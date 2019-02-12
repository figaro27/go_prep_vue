<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use App\UserDetail;
use App\Store;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
     */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $v = Validator::make($data, [
            //'name' => 'required|string|max:255',
            'user.role' => 'required|in:customer,store',
            'user.email' => 'required|string|email|max:255|unique:users,email',
            'user.password' => 'required|string|min:6|confirmed',

            'user_details.first_name' => 'required',
            'user_details.last_name' => 'required',
            'user_details.phone' => 'required',
            'user_details.address' => 'required',
            'user_details.city' => 'required',
            'user_details.state' => 'required',
            'user_details.zip' => 'required',
        ]);

        $v->sometimes('store', [
          'store.store_name' => 'required',
          'store.phone' => 'required',
          'store.address' => 'required',
          'store.city' => 'required',
          'store.state' => 'required',
          'store.zip' => 'required',
        ], function ($data) {
          return $data['user']['role'] === 'store';
        });

        return $v;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            //'name' => $data['name'],
            'user_role_id' => ($data['user']['role'] === 'store' ? 2 : 1),
            'email' => $data['user']['email'],
            'password' => Hash::make($data['user']['password']),
            'timezone' => 'EST',
            'remember_token' => Hash::make(str_random(10)),
        ]);

        $userDetails = $user->details()->create([
            //'user_id' => $user->id,
            'firstname' => $data['user_details']['first_name'],
            'lastname' => $data['user_details']['last_name'],
            'phone' => $data['user_details']['phone'],
            'address' => $data['user_details']['address'],
            'city' => $data['user_details']['city'],
            'state' => $data['user_details']['state'],
            'zip' => $data['user_details']['zip'],
            'country' => 'USA',
            'delivery' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($data['user']['role'] === 'store') {
            $store = $user->store()->create([]);

            $storeDetail = $store->details()->create([
                'name' => $data['store']['store_name'],
                'phone' => $data['store']['phone'],
                'address' => $data['store']['address'],
                'city' => $data['store']['city'],
                'state' => $data['store']['state'],
                'zip' => $data['store']['zip'],
                'logo' => '',
                'domain' => str_slug($data['store']['store_name']),
                'created_at' => now(),
            ]);
        }

        return $user;
    }

    protected function registered(Request $request, $user) {
      // Create auth token
      $token = auth()->login($user);

      $redirect = $user->hasRole('store') ? $user->store->getUrl('/store/account/settings') : '/customer/home';

      return [
        'user' => $user,
        'access_token' => $token,
        'token_type' => 'bearer',
        'expires_in' => auth()->factory()->getTTL() * 60,
        'redirect' => $redirect
      ];
    }
}
