<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingCurrencyFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::table('orders', function (Blueprint $table) {
                $table
                    ->string('currency', 5)
                    ->default('USD')
                    ->after('amount');
            });

            Schema::table('subscriptions', function (Blueprint $table) {
                $table
                    ->string('currency', 5)
                    ->default('USD')
                    ->after('amount');
            });
        } catch (\Exception $e) {
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
}
