<?php

namespace Tests\Unit;

use App\Billing\Authorize;
use App\Customer;
use App\Store;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BillingTest extends TestCase
{
    /**
     * @var \App\Billing\IBilling
     */
    protected $gateway = null;

    /**
     * @var \App\Billing\Stripe
     */
    protected $stripe = null;

    /**
     * @var \App\Billing\Authorize
     */
    protected $authorize = null;

    /**
     * @var \App\Customer
     */
    protected $customer = null;

    /**
     * @var string
     */
    protected $customerId = null;

    protected function setUp()
    {
        parent::setUp();

        $this->store = Store::find(1);
        $this->authorize = new Authorize($this->store);
        $this->gateway = $this->authorize;
        $this->gateway->setSandbox(true);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateCustomer()
    {
        $user = User::where('user_role_id', 1)->first();
        $customerId = $this->gateway->createCustomer($user);

        $this->assertNotEmpty($customerId);

        $_customer = $this->gateway->getCustomer($customerId);
        $this->assertNotEmpty($_customer);
        $this->assertEquals($_customer->getCustomerProfileId(), $customerId);

        // Create goprep customer
        $customer = new Customer();
        $customer->user_id = $user->id;
        $customer->store_id = $this->store->id;
        $customer->stripe_id = $customerId;
        $customer->currency = 'USD';
        $customer->save();

        return $customer;
    }

    /**
     * @depends testCreateCustomer
     * @param \App\Customer $customer
     */
    public function testCreateCard($customer)
    {
        $customerId = $customer->stripe_id;

        $cardNumber = $this->gateway::TEST_CARD_NUMBER;
        $cardExpiration = $this->gateway::TEST_CARD_EXPIRATION;
        $cardSecurity = $this->gateway::TEST_CARD_SECURITY;

        $response = $this->gateway->createCard(
            $customer,
            $cardNumber,
            $cardExpiration,
            $cardSecurity
        );

        dd($response);
    }
}
