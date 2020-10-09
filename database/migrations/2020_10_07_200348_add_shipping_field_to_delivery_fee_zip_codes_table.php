<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShippingFieldToDeliveryFeeZipCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_fee_zip_codes', function (Blueprint $table) {
            $table
                ->boolean('shipping')
                ->after('delivery_fee')
                ->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_fee_zip_codes', function (Blueprint $table) {
            $table->dropColumn('shipping');
        });
    }
}
