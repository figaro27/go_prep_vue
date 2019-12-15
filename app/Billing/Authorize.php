<?php

namespace App\Billing;

use App\Billing\Card;
use App\Billing\Subscription;
use App\Billing\Exceptions\BillingException;
use App\Store;
use App\Customer;
use App\User;

use net\authorize\api\contract\v1 as AnetApi;
use net\authorize\api\contract\v1\ANetApiResponseType;
use net\authorize\api\controller as AnetController;
use net\authorize\api\contract\v1\MerchantAuthenticationType;

class Authorize implements IBilling
{
    public static $id = 'authorize';
    protected $sandbox = false;
    protected $environment = \net\authorize\api\constants\ANetEnvironment::PRODUCTION;

    const TEST_CARD_NUMBER = '4242424242424242';
    const TEST_CARD_EXPIRATION = '2032-10';
    const TEST_CARD_SECURITY = '123';

    /**
     * @var AnetAPI\MerchantAuthenticationType
     */
    protected $authContext = null;

    public function __construct(Store $store = null)
    {
        if ($store) {
            $this->setAuthContext($store);
        }

        if (env('APP_ENV') !== 'production') {
            $this->setSandbox();
        }
    }

    public function setSandbox()
    {
        $this->sandbox = true;
        $this->environment =
            \net\authorize\api\constants\ANetEnvironment::SANDBOX;
    }

    public function setAuthContext(Store $store)
    {
        $storeSettings = $store->settings;
        $loginId = $storeSettings->authorize_login_id ?? null;
        $transactionKey = $storeSettings->authorize_transaction_key ?? null;

        if (!$loginId || !$transactionKey) {
            throw new BillingException('Invalid Authorize.net credentials');
        }

        $merchantAuthentication = new MerchantAuthenticationType();
        $merchantAuthentication->setName($loginId);
        $merchantAuthentication->setTransactionKey($transactionKey);

        $this->authContext = $merchantAuthentication;

        return $merchantAuthentication;
    }

    protected function validateResponse(ANetApiResponseType $response)
    {
        if (
            $response != null &&
            $response->getMessages()->getResultCode() == "Ok"
        ) {
            return $response;
        } elseif (!$response) {
            throw new BillingException("Invalid response from Authorize.net");
        } else {
            $errorMessages = $response->getMessages()->getMessage();
            $message = $errorMessages
                ? $errorMessages[0]->getText()
                : 'Authorize billing exception';
            $code = $errorMessages[0]->getCode();
            throw new BillingException($message, $code);
        }
    }

    /**
     * @return App\Billing\Card
     */
    public function createCard(Customer $customer, string $token)
    {
        $user = $customer->user;

        $opaque = new AnetApi\OpaqueDataType();
        $opaque->setDataDescriptor('COMMON.ACCEPT.INAPP.PAYMENT');
        $opaque->setDataValue($token);

        $paymentCreditCard = new AnetAPI\PaymentType();
        $paymentCreditCard->setOpaqueData($opaque);

        // Create the Bill To info for new payment type

        $firstName = $user->details->firstname;
        $lastName = $user->details->lastname;
        $address = $user->details->billingAddress
            ? $user->details->billingAddress
            : $user->details->address;
        $city = $user->details->billingCity
            ? $user->details->billingCity
            : $user->details->city;
        $state = $user->details->billingState
            ? $user->details->billingState
            : $user->details->state;
        $zip = $user->details->billingZip
            ? $user->details->billingZip
            : $user->details->zip;
        $country = $user->details->country;
        $phone = $user->details->phone;

        $billto = new AnetAPI\CustomerAddressType();
        $billto->setFirstName($firstName);
        $billto->setLastName($lastName);
        $billto->setAddress($address);
        $billto->setCity($city);
        $billto->setState($state);
        $billto->setZip($zip);
        $billto->setCountry($country);
        $billto->setPhoneNumber($phone);

        // Create a new Customer Payment Profile object
        $paymentprofile = new AnetAPI\CustomerPaymentProfileType();
        $paymentprofile->setCustomerType('individual');
        $paymentprofile->setPayment($paymentCreditCard);
        $paymentprofile->setDefaultPaymentProfile(true);
        $paymentprofile->setBillTo($billto);

        $paymentprofiles = [$paymentprofile];

        // Assemble the complete transaction request
        $paymentprofilerequest = new AnetAPI\CreateCustomerPaymentProfileRequest();
        $paymentprofilerequest->setMerchantAuthentication($this->authContext);

        // Add an existing profile id to the request
        $paymentprofilerequest->setCustomerProfileId($customer->stripe_id);
        $paymentprofilerequest->setPaymentProfile($paymentprofile);
        $paymentprofilerequest->setValidationMode(
            $this->sandbox ? "testMode" : "liveMode"
        );

        // Create the controller and get the response
        $controller = new AnetController\CreateCustomerPaymentProfileController(
            $paymentprofilerequest
        );
        $response = $controller->executeWithApiResponse($this->environment);

        try {
            /**
             * @var AnetApi\CreateCustomerPaymentProfileResponse
             */
            $response = $this->validateResponse($response);
        } catch (BillingException $e) {
            // If a duplicate payment profile was found,
            // skip exception and return the existing profile
            if ($e->code !== 'E00039') {
                throw $e;
            }
        }

        $card = new Card();
        $card->id = $response->getCustomerPaymentProfileId();

        return $card;
    }

    /**
     * @return string The customer ID
     */
    public function createCustomer(User $user)
    {
        $customerProfile = new AnetApi\CustomerProfileType();
        $customerProfile->setDescription('');
        $customerProfile->setMerchantCustomerId("M_" . time());
        $customerProfile->setEmail($user->email);

        //$customerProfile->setpaymentProfiles($paymentProfiles);
        //$customerProfile->setShipToList($shippingProfiles);

        $request = new AnetApi\CreateCustomerProfileRequest();
        $request->setMerchantAuthentication($this->authContext);
        $request->setRefId($user->id);
        $request->setProfile($customerProfile);
        $controller = new AnetController\CreateCustomerProfileController(
            $request
        );

        /**
         * @var $response CreateCustomerProfileResponse
         */
        $response = $controller->executeWithApiResponse($this->environment);

        $this->validateResponse($response);

        return $response->getCustomerProfileId();
    }

    /**
     * @return AnetApi\CustomerProfileMaskedType
     */
    public function getCustomer($profileId)
    {
        $request = new AnetApi\GetCustomerProfileRequest();
        $request->setMerchantAuthentication($this->authContext);
        $request->setCustomerProfileId($profileId);
        $controller = new AnetController\GetCustomerProfileController($request);
        $response = $controller->executeWithApiResponse($this->environment);

        return $response->getProfile();
    }

    public function charge(Charge $charge)
    {
        $customer = $charge->customer;
        $amount = $charge->amount / 100;
        $card = $charge->card;
        $refId = $charge->refId ?? 'ref' . time();

        $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
        $profileToCharge->setCustomerProfileId($customer->stripe_id);
        $paymentProfile = new AnetAPI\PaymentProfileType();
        $paymentProfile->setPaymentProfileId($card->stripe_id);
        $profileToCharge->setPaymentProfile($paymentProfile);

        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($amount);
        $transactionRequestType->setProfile($profileToCharge);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($this->authContext);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequestType);
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse($this->environment);

        $this->validateResponse($response);

        return $response->getTransactionResponse()->getTransId();
    }

    public function refund(Refund $refund)
    {
        $customer = $refund->customer;
        $amount = $refund->amount / 100;
        $card = $refund->card;
        $refId = $refund->refId ?? 'ref' . time();

        $profileToRefund = new AnetAPI\CustomerProfilePaymentType();
        $profileToRefund->setCustomerProfileId($customer->stripe_id);
        $paymentProfile = new AnetAPI\PaymentProfileType();
        $paymentProfile->setPaymentProfileId($card->stripe_id);
        $profileToRefund->setPaymentProfile($paymentProfile);

        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("refundTransaction");
        $transactionRequestType->setAmount($amount);
        $transactionRequestType->setProfile($profileToRefund);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($this->authContext);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequestType);
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse($this->environment);

        $this->validateResponse($response);

        return $response->getTransactionResponse()->getTransId();
    }

    /**
     * @param
     */
    public function subscribe(Subscription $subscription)
    {
        $customer = $subscription->customer;
        $amount = $subscription->amount;
        $card = $subscription->card;
        $period = $subscription->period;
        $startDate = $subscription->startDate;
        $refId = $subscription->refId ?? 'ref' . time();

        // Subscription Type Info
        $subscription = new AnetAPI\ARBSubscriptionType();
        $subscription->setName("GoPrep weekly subscription");

        $interval = new AnetAPI\PaymentScheduleType\IntervalAType();
        $interval->setLength($period === Constants::PERIOD_MONTHLY ? 1 : 7);
        $interval->setUnit(
            $period === Constants::PERIOD_MONTHLY ? 'months' : 'days'
        );

        $paymentSchedule = new AnetAPI\PaymentScheduleType();
        $paymentSchedule->setInterval($interval);
        $paymentSchedule->setStartDate(new \DateTime($startDate->toString()));

        $subscription->setPaymentSchedule($paymentSchedule);
        $subscription->setAmount($amount);
        $subscription->setTrialAmount("0.00");

        $profile = new AnetAPI\CustomerProfileIdType();
        $profile->setCustomerProfileId($customer->stripe_id);
        $profile->setCustomerPaymentProfileId($card->stripe_id);
        //$profile->setCustomerAddressId($customerAddressId);

        $subscription->setProfile($profile);

        $request = new AnetAPI\ARBCreateSubscriptionRequest();
        $request->setmerchantAuthentication($this->authContext);
        $request->setRefId($refId);
        $request->setSubscription($subscription);
        $controller = new AnetController\ARBCreateSubscriptionController(
            $request
        );

        $response = $controller->executeWithApiResponse($this->environment);

        $this->validateResponse($response);

        return $response->getTransactionResponse()->getTransId();
    }

    /**
     * @return AnetApi\GetMerchantDetailsResponse
     */
    public function getMerchantDetails()
    {
        $request = new AnetAPI\GetMerchantDetailsRequest();
        $request->setMerchantAuthentication($this->authContext);

        $controller = new AnetController\GetMerchantDetailsController($request);

        /**
         * @var AnetApi\GetMerchantDetailsResponse
         */
        $response = $controller->executeWithApiResponse($this->environment);

        $this->validateResponse($response);

        return $response;
    }

    public function getPublicClientKey()
    {
        $merchant = $this->getMerchantDetails();
        return $merchant->getPublicClientKey();
    }
}
