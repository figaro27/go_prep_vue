<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMappingIdColumnToMealPackageSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meal_package_subscriptions', function (
            Blueprint $table
        ) {
            $table
                ->string('mappingId')
                ->after('customSize')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meal_package_subscriptions', function (
            Blueprint $table
        ) {
            $table->dropColumn('mappingId');
        });
    }
}
