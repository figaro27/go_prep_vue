<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOptionRestrictionsToModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_modules', function (Blueprint $table) {
            $table
                ->boolean('package_component_restrictions')
                ->default(0)
                ->after('allowCashOrders')
                ->comment(
                    'Controls whether meal package component options can be restricted to meals in the other option'
                );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_modules', function (Blueprint $table) {
            $table->dropColumn('package_component_restrictions');
        });
    }
}
