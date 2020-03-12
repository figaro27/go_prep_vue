<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\ReferralSetting;
use App\Store;

class CreateReferralSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('store_id');
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
            $table->boolean('enabled')->default(0);
            $table->boolean('signupEmail')->default(1);
            $table->boolean('showInNotifications')->default(1);
            $table->boolean('showInMenu')->default(1);
            $table->string('frequency')->default('urlOnly');
            $table->string('type')->default('percent');
            $table->decimal('amount')->default(5.0);
            $table->timestamps();
        });

        $stores = Store::all();
        foreach ($stores as $store) {
            ReferralSetting::create([
                'store_id' => $store->id,
                'enabled' => 0,
                'signupEmail' => 1,
                'showInNotifications' => 1,
                'showInMenu' => 1,
                'frequency' => 'urlOnly',
                'type' => 'type',
                'amount' => 5.0
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
        Schema::dropIfExists('referral_settings');
    }
}
