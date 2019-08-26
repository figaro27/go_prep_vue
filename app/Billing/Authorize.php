<?php

namespace App\Billing;

use App\Billing\Exceptions\BillingException;
use App\Store;
use App\Customer;
use App\User;

use net\authorize\api\contract\v1 as AnetApi;
use net\authorize\api\controller as AnetController;

use net\authorize\api\contract\v1\PaymentType;
use net\authorize\api\constants\ANetEnvironment;
use net\authorize\api\contract\v1\CreditCardType;
use net\authorize\api\contract\v1\MerchantAuthenticationType;
use net\authorize\api\controller\GetCustomerProfileController;

use net\authorize\api\controller\CreateCustomerProfileController;
use net\authorize\api\contract\v1\CustomerProfileType;
use net\authorize\api\contract\v1\ANetApiRequestType;
use net\authorize\api\contract\v1\ANetApiResponseType;
use net\authorize\api\contract\v1\CreateCustomerProfileRequest;
use net\authorize\api\contract\v1\GetCustomerProfileRequest;
use net\authorize\api\controller;

class Authorize implements IBilling
{
    public static $id = 'authorize';
    protected $sandbox = false;
    protected $environment = \net\authorize\api\constants\ANetEnvironment::SANDBOX;

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
    }

    public function setSandbox($sandbox)
    {
        $this->sandbox = true;
    }

    public function setAuthContext(Store $store)
    {
        $loginId = config('services.authorize.login_id');
        $transactionKey = config('services.authorize.transaction_key');

        $merchantAuthentication = new MerchantAuthenticationType();
        $merchantAuthentication->setName($loginId);
        $merchantAuthentication->setTransactionKey($transactionKey);

        $this->authContext = $merchantAuthentication;

        return $merchantAuthentication;
    }

    protected function validateResponse($response)
    {
        if (
            $response != null &&
            $response->getMessages()->getResultCode() == "Ok"
        ) {
            return $response;
        } else {
            $errorMessages = $response->getMessages()->getMessage();
            throw new BillingException($errorMessages);
        }
    }

    /**
     * @return CreateCustomerPaymentProfileResponse
     */
    public function createCard(Customer $customer, string $token)
    {
        // Check that customer has an Authorize profile

        // Set credit card information for payment profile
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($cardNumber);
        $creditCard->setExpirationDate($expiration);
        $creditCard->setCardCode($securityCode);
        $paymentCreditCard = new AnetAPI\PaymentType();
        $paymentCreditCard->setCreditCard($creditCard);

        // Create the Bill To info for new payment type
        /*$billto = new AnetAPI\CustomerAddressType();
        $billto->setFirstName("Ellen".$phoneNumber);
        $billto->setLastName("Johnson");
        $billto->setCompany("Souveniropolis");
        $billto->setAddress("14 Main Street");
        $billto->setCity("Pecan Springs");
        $billto->setState("TX");
        $billto->setZip("44628");
        $billto->setCountry("USA");
        $billto->setPhoneNumber($phoneNumber);
        $billto->setfaxNumber("999-999-9999");*/

        // Create a new Customer Payment Profile object
        $paymentprofile = new AnetAPI\CustomerPaymentProfileType();
        $paymentprofile->setCustomerType('individual');
        //$paymentprofile->setBillTo($billto);
        $paymentprofile->setPayment($paymentCreditCard);
        $paymentprofile->setDefaultPaymentProfile(true);

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
        $response = $this->validateResponse($response);

        $card = new Card();
        $card->id = 123;

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
        //$customerProfile->setEmail('');
        //$customerProfile->setpaymentProfiles($paymentProfiles);
        //$customerProfile->setShipToList($shippingProfiles);

        $request = new AnetApi\CreateCustomerProfileRequest();
        $request->setMerchantAuthentication($this->authContext);
        $request->setRefId('123');
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

    public function charge(Customer $customer, int $amount, Card $card = null)
    {
    }

    public function createSubscription()
    {
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
