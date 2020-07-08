<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Store;
use App\SmsSetting;

class SeedAutoSendSubscriptionTemplateOnExistingStores extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $stores = Store::all();
        foreach ($stores as $store) {
            $smsSetting = SmsSetting::where('store_id', $store->id)->first();
            $smsSetting->update([
                'autoSendSubscriptionRenewalTemplate' =>
                    'Your subscription from {store name} will renew in 24 hours. If you\'d like to make any changes, please visit {URL}.'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
