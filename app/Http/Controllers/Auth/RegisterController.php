<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Store;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

    protected $regex = [
        'domain' => '/^[A-Za-z0-9](?:[A-Za-z0-9\-]{0,61}[A-Za-z0-9])?$/is',
    ];

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
            'user.first_name' => 'required',
            'user.last_name' => 'required',
            'user.phone' => 'required',

            'user_details.address' => 'required',
            'user_details.city' => 'required',
            'user_details.state' => 'required',
            'user_details.zip' => 'required',
        ]);

        $v->sometimes('store', [
            'store.store_name' => ['required', 'unique:store_details,name'],
            'store.domain' => ['required', 'unique:store_details,domain', 'regex:' . $this->regex['domain']],
            'store.address' => 'required',
            'store.city' => 'required',
            'store.state' => 'required',
            'store.zip' => 'required',
        ], function ($data) {
            return $data['user']['role'] === 'store';
        });

        return $v;
    }

    public function validateStep(Request $request, $step)
    {
        switch ($step) {
            case '0':
                $v = Validator::make($request->all(), [
                    'role' => 'required|in:customer,store',
                    'email' => 'required|string|email|max:255|unique:users,email',
                    'password' => 'required|string|min:6|confirmed',
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'phone' => 'required',
                ]);
                break;

            case '1':
                $v = Validator::make($request->all(), [
                    'address' => 'required',
                    'city' => 'required',
                    'state' => 'required',
                    'zip' => 'required',
                ]);
                break;

            case '2':
                $v = Validator::make($request->all(), [
                    'store_name' => ['required', 'unique:store_details,name'],
                    'domain' => ['required', 'unique:store_details,domain', 'regex:' . $this->regex['domain']],
                    'address' => 'required',
                    'city' => 'required',
                    'state' => 'required',
                    'zip' => 'required',
                ]);
                break;
        }

        return $v->validate();
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
            'firstname' => $data['user']['first_name'],
            'lastname' => $data['user']['last_name'],
            'phone' => $data['user']['phone'],
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
                'phone' => $data['user']['phone'],
                'address' => $data['store']['address'],
                'city' => $data['store']['city'],
                'state' => $data['store']['state'],
                'zip' => $data['store']['zip'],
                'logo' => '',
                'domain' => $data['store']['domain'],
                'created_at' => now(),
            ]);

            $storeSettings = $store->settings()->create([
                'timezone' => 'EST',
                'open' => 0,
                'notifications' => [],
                'transferType' => 'delivery',
                'view_delivery_days' => 1,
                'delivery_days' => [],
                'delivery_distance_zipcodes' => [],
            ]);

            $key = new \Cloudflare\API\Auth\APIKey(config('services.cloudflare.user'), config('services.cloudflare.key'));
            $adapter = new \Cloudflare\API\Adapter\Guzzle($key);
            $zones = new \Cloudflare\API\Endpoints\Zones($adapter);
            $dns = new \Cloudflare\API\Endpoints\DNS($adapter);

            $zoneId = $zones->getZoneID('goprep.com');

            $dns->addRecord($zoneId, 'CNAME', $storeDetail->domain.'.dev', 'goprep.com', 0, true);
            $dns->addRecord($zoneId, 'CNAME', $storeDetail->domain, 'goprep.com', 0, true);
        }

        return $user;
    }

    protected function registered(Request $request, $user)
    {
        // Create auth token
        $token = auth()->login($user);

        $redirect = $user->hasRole('store') ? '/store/account/settings' : '/customer/home';

        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'redirect' => $redirect,
        ];
    }
}
