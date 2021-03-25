<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToStorePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('store_plans', function (Blueprint $table) {
            $table
                ->unsignedInteger('store_id')
                ->references('id')
                ->on('stores')
                ->change();
            $table
                ->foreign('store_id')
                ->references('id')
                ->on('stores');
            $table
                ->string('store_name')
                ->after('store_id')
                ->nullable();
            $table
                ->string('contact_name')
                ->after('store_name')
                ->nullable();
            $table
                ->string('contact_email')
                ->after('store_name')
                ->nullable();
            $table
                ->string('contact_phone')
                ->after('contact_email')
                ->nullable();
            $table
                ->boolean('free_trial')
                ->after('contact_name')
                ->default(0);
            $table
                ->string('status')
                ->after('store_name')
                ->default('active');
            $table
                ->string('plan_name')
                ->after('active')
                ->default('basic');
            $table
                ->string('plan_notes')
                ->after('plan_name')
                ->nullable();
            $table
                ->unsignedInteger('allowed_orders')
                ->after('plan_name')
                ->default(50);
            $table
                ->string('joined_store_ids')
                ->after('allowed_orders')
                ->nullable();
            $table
                ->unsignedInteger('last_month_total_orders')
                ->after('allowed_orders')
                ->default(0);
            $table
                ->unsignedInteger('months_over_limit')
                ->after('last_month_total_orders')
                ->default(0);
            $table
                ->dateTime('cancelled_at')
                ->after('stripe_subscription_id')
                ->nullable();
            $table
                ->string('cancellation_reason')
                ->after('cancelled_at')
                ->nullable();
            $table
                ->string('cancellation_additional_info')
                ->after('cancellation_reason')
                ->nullable();
            $table->dateTime('charge_failed')->nullable();
            $table->string('charge_failed_reason')->nullable();
            $table->unsignedInteger('charge_attempts')->default(0);
            $table
                ->unsignedInteger('month')
                ->after('day')
                ->nullable();
            $table
                ->string('stripe_card_id')
                ->after('stripe_subscription_id')
                ->nullable();
            $table
                ->dateTime('last_charged')
                ->nullable()
                ->change();
            $table->dropColumn('active');
        });
        Schema::disableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_plans', function (Blueprint $table) {
            //
        });
    }
}
