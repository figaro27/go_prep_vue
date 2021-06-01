<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\SmsList;
use App\SmsContact;
use stdClass;
use Carbon\Carbon;
use App\Order;

class SmsSetting extends Model
{
    protected $headers = [
        'X-TM-Username' => 'mikesoldano',
        'X-TM-Key' => 'sYWo6q3SVtDr9ilKAIzo4XKL4lKVHg',
        'Content-Type' => 'application/x-www-form-urlencoded'
    ];

    protected $casts = [
        'autoAddCustomers' => 'boolean',
        'autoSendDelivery' => 'boolean',
        'autoSendOrderReminder' => 'boolean',
        'autoSendOrderConfirmation' => 'boolean',
        'autoSendSubscriptionRenewal' => 'boolean',
        'notifyChats' => 'boolean'
    ];

    public $appends = [
        'nextDeliveryDate',
        'nextCutoff',
        'orderReminderTime',
        'orderReminderTemplatePreview',
        'orderConfirmationTemplatePreview',
        'deliveryTemplatePreview',
        'subscriptionTemplatePreview',
        'aboveFiftyContacts',
        'subscription'
    ];

    protected $guarded = [
        'id',
        'store',
        'nextDeliveryDate',
        'nextCutoff',
        'orderReminderTime',
        'orderReminderTemplatePreview',
        'orderConfirmationTemplatePreview',
        'deliveryTemplatePreview',
        'subscriptionTemplatePreview',
        'aboveFiftyContacts'
    ];

    public function store()
    {
        return $this->belongsTo('App\Store');
    }

    public function cancelSMSNumber()
    {
        $phoneId = $this->textmagic_phone_id;

        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'DELETE',
            'https://rest.textmagic.com/api/v2/numbers/' . $phoneId,
            [
                'headers' => $this->headers
            ]
        );

        $this->last_payment = null;
        $this->update();
    }

    public function addNewCustomerToContacts($customer)
    {
        $contact = $customer;

        $listId = SmsList::where('store_id', $this->store->id)
            ->pluck('list_id')
            ->first();

        if (!$listId) {
            $listId = $this->createMasterList();
        }

        $phone = (int) preg_replace('/[^0-9]/', '', $contact['phone']);
        if (strlen((string) $phone) === 10) {
            $phone = 1 . $phone;
        }

        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->request(
                'POST',
                'https://rest.textmagic.com/api/v2/contacts',
                [
                    'headers' => $this->headers,
                    'form_params' => [
                        'phone' => $phone,
                        'lists' => $listId,
                        'firstName' => $contact['firstname'],
                        'lastName' => $contact['lastname']
                    ]
                ]
            );
            $status = $res->getStatusCode();
            $body = $res->getBody();
            $smsContactId = json_decode($body)->id;

            $smsContact = new SmsContact();
            $smsContact->store_id = $this->store->id;
            $smsContact->contact_id = $smsContactId;
            $smsContact->save();
        } catch (\Exception $e) {
        }
    }

    public function sendOrderReminderSMS($store)
    {
        $list = SmsList::where('store_id', $store->id)
            ->pluck('list_id')
            ->first();

        $message = $this->processTags(
            $this->autoSendOrderReminderTemplate,
            false
        );

        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->request(
                'POST',
                'https://rest.textmagic.com/api/v2/messages',
                [
                    'headers' => $this->headers,
                    'form_params' => [
                        'lists' => $list,
                        'text' => $message,
                        'from' => $this->phone
                    ]
                ]
            );
            $status = $res->getStatusCode();
            $body = $res->getBody();

            // Get number of contacts in the list to calculate charge
            $client = new \GuzzleHttp\Client();
            $res = $client->request('GET', $this->baseURL . '/' . $list, [
                'headers' => $this->headers
            ]);
            $body = $res->getBody();
            $contacts = json_decode($body)->membersCount;

            // Should figure out the parts
            $this->balance += $contacts * 0.06;
            $this->total_spent += $contacts * 0.06;
            $this->update();
            $this->chargeBalance($store);

            return $body;
        } catch (\Exception $e) {
        }
    }

    public function sendOrderConfirmationSMS($customer, $order)
    {
        $pickup = $order['pickup'] ? true : false;
        $deliveryDate = new Carbon($order['delivery_date']);
        $deliveryDate = $deliveryDate->format('l, M d');

        $message = $this->processTags(
            $this->autoSendOrderConfirmationTemplate,
            false,
            $pickup,
            $deliveryDate
        );

        $phone = (int) preg_replace('/[^0-9]/', '', $customer['phone']);
        if (strlen((string) $phone) === 10) {
            $phone = 1 . $phone;
        }

        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->request(
                'POST',
                'https://rest.textmagic.com/api/v2/messages',
                [
                    'headers' => $this->headers,
                    'form_params' => [
                        'phones' => $phone,
                        'text' => $message,
                        'from' => $this->phone
                    ]
                ]
            );
            $status = $res->getStatusCode();
            $body = $res->getBody();

            $store = $this->store;
            $this->chargeBalance($store);

            return $body;
        } catch (\Exception $e) {
        }
    }

    public function sendDeliverySMS($order)
    {
        $customer = $order->customer;
        $pickup = $order['pickup'] ? true : false;

        $message = $this->processTags(
            $this->autoSendDeliveryTemplate,
            false,
            $pickup
        );

        $phone = (int) preg_replace('/[^0-9]/', '', $customer['phone']);
        if (strlen((string) $phone) === 10) {
            $phone = 1 . $phone;
        }

        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->request(
                'POST',
                'https://rest.textmagic.com/api/v2/messages',
                [
                    'headers' => $this->headers,
                    'form_params' => [
                        'phones' => $phone,
                        'text' => $message,
                        'from' => $this->phone
                    ]
                ]
            );
            $status = $res->getStatusCode();
            $body = $res->getBody();

            $store = $this->store;
            $this->balance += 0.06;
            $this->total_spent += 0.06;
            $this->update();
            $this->chargeBalance($store);

            return $body;
        } catch (\Exception $e) {
        }
    }

    public function sendSubscriptionRenewalSMS($subscription)
    {
        $customer = $subscription->customer;

        $message = $this->processTags(
            $this->autoSendSubscriptionRenewalTemplate,
            false
        );

        $phone = (int) preg_replace('/[^0-9]/', '', $customer['phone']);
        if (strlen((string) $phone) === 10) {
            $phone = 1 . $phone;
        }

        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->request(
                'POST',
                'https://rest.textmagic.com/api/v2/messages',
                [
                    'headers' => $this->headers,
                    'form_params' => [
                        'phones' => $phone,
                        'text' => $message,
                        'from' => $this->phone
                    ]
                ]
            );
            $status = $res->getStatusCode();
            $body = $res->getBody();

            $store = $this->store;
            $this->balance += 0.06;
            $this->total_spent += 0.06;
            $this->update();
            $this->chargeBalance($store);

            return $body;
        } catch (\Exception $e) {
        }
    }

    public function chargeBalance($store)
    {
        if ($store->settings->account_type === 'express') {
            if ($this->balance >= 10) {
                $charge = \Stripe\Charge::create([
                    'amount' => round($this->balance * 100),
                    'currency' => $store->settings->currency,
                    'source' => $store->settings->stripe_id,
                    'description' =>
                        'SMS fee balance for ' . $store->storeDetail->name
                ]);
                $this->balance = 0;
                $this->update();
            }
        } else {
            $plan = \Stripe\Plan::create([
                // Keep the $8.00 fee for the phone number in the amount
                'amount' => 800 + $this->balance * 100,
                'currency' => 'usd',
                'interval' => 'month',
                'product' => env('PRODUCT_SMS_MESSAGE_BALANCE')
            ]);

            $subscription = \Stripe\Subscription::retrieve(
                $this->stripe_subscription_id
            );

            $subscription = \Stripe\Subscription::update($subscription->id, [
                'cancel_at_period_end' => false,
                'items' => [
                    [
                        'id' => $subscription->items->data[0]->id,
                        'plan' => $plan->id
                    ]
                ],
                'prorate' => false
            ]);

            return $subscription;
        }
    }

    public function getNextDeliveryDateAttribute()
    {
        $nextDeliveryDates = $this->store->settings->getNextDeliveryDates(true);
        $nextPickupDates = $this->store->settings->getNextDeliveryDates(
            true,
            'pickup'
        );
        $nextDates = $nextDeliveryDates->merge($nextPickupDates);

        foreach ($nextDates as $nextDate) {
            $storeSettings = $this->store->settings;
            $nextCutoff = $storeSettings
                ->getCutoffDate($nextDate)
                ->setTimezone($storeSettings->timezone);
            $reminderTime = $nextCutoff->subHours(
                $this->autoSendOrderReminderHours
            );
            if (!$reminderTime->isPast()) {
                // Not the next available delivery date but the next delivery date in which factoring the cutoff and reminder time is in the future
                return $nextDate;
            }
        }

        // If there are no adjusted next delivery dates in the future (factoring in cutoff and order reminder hours), then take the next upcoming date and add 1 week.
        if (count($nextDeliveryDates) > 0) {
            return $nextDeliveryDates[0]->addWeeks(1);
        }
    }

    public function getNextCutoffAttribute()
    {
        if ($this->nextDeliveryDate) {
            $storeSettings = $this->store->settings;
            return $storeSettings
                ->getCutoffDate($this->nextDeliveryDate)
                ->setTimezone($storeSettings->timezone);
        }
    }

    public function getOrderReminderTimeAttribute()
    {
        if ($this->nextCutoff) {
            return $this->nextCutoff->subHours(
                $this->autoSendOrderReminderHours
            );
        }
    }

    public function getOrderReminderTemplatePreviewAttribute()
    {
        return $this->processTags($this->autoSendOrderReminderTemplate);
    }

    public function getOrderConfirmationTemplatePreviewAttribute()
    {
        return $this->processTags($this->autoSendOrderConfirmationTemplate);
    }

    public function getDeliveryTemplatePreviewAttribute()
    {
        return $this->processTags($this->autoSendDeliveryTemplate);
    }

    public function getSubscriptionTemplatePreviewAttribute()
    {
        return $this->processTags($this->autoSendSubscriptionRenewalTemplate);
    }

    public function getSubscriptionAttribute()
    {
        if ($this->store->settings->account_type === 'standard') {
            if ($this->stripe_subscription_id) {
                return \Stripe\Subscription::retrieve(
                    $this->stripe_subscription_id
                );
            }
        }
    }

    public function getAboveFiftyContactsAttribute()
    {
        $count = SmsContact::where('store_id', $this->store->id)->count();
        if ($count > 50) {
            return true;
        } else {
            return false;
        }
    }

    public function createMasterList()
    {
        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'POST',
            'https://rest.textmagic.com/api/v2/lists',
            [
                'headers' => $this->headers,
                'form_params' => [
                    'name' => 'All Contacts - ' . $this->store->details->name
                ]
            ]
        );
        $status = $res->getStatusCode();
        $body = $res->getBody();

        $smsList = new SmsList();
        $smsList->store_id = $this->store->id;
        $smsList->list_id = json_decode($body)->id;
        $smsList->save();

        return json_decode($body)->id;
    }

    public function processTags(
        $template,
        $preview = true,
        $pickup = false,
        $deliveryDate = null
    ) {
        if ($this->nextDeliveryDate) {
            if (strpos($template, '{store name}')) {
                $processedTag = $this->store->details->name;
                $template = str_replace(
                    '{store name}',
                    $processedTag,
                    $template
                );
            }

            if (strpos($template, '{URL}')) {
                $processedTag = $this->store->details->full_URL;
                $template = str_replace('{URL}', $processedTag, $template);
            }

            if (strpos($template, '{cutoff}')) {
                $processedTag =
                    $this->nextCutoff->format('l, M d') .
                    ' at ' .
                    $this->nextCutoff->format('g a');
                $template = str_replace('{cutoff}', $processedTag, $template);
            }

            if (strpos($template, '{next delivery}')) {
                $processedTag = $this->nextDeliveryDate->format('l, M d');
                $template = str_replace(
                    '{next delivery}',
                    $processedTag,
                    $template
                );
            }

            if (strpos($template, '{delivery date}')) {
                if ($preview) {
                    $processedTag = '(delivery date)';
                } else {
                    $processedTag = $deliveryDate;
                }

                $template = str_replace(
                    '{delivery date}',
                    $processedTag,
                    $template
                );
            }

            if (strpos($template, '{pickup/delivery}')) {
                $processedTag = '';
                if ($preview) {
                    $processedTag =
                        '(is available for pickup / will be delivered)';
                } else {
                    if ($pickup) {
                        $processedTag = 'is available for pickup';
                    } else {
                        $processedTag = 'will be delivered';
                    }
                }
                $template = str_replace(
                    '{pickup/delivery}',
                    $processedTag,
                    $template
                );
            }
            return $template;
        }
    }
}
