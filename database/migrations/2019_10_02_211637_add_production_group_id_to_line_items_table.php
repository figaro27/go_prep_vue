<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProductionGroupIdToLineItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('line_items', function (Blueprint $table) {
            $table
                ->unsignedInteger('production_group_id')
                ->references('id')
                ->on('production_groups')
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
        Schema::table('line_items', function (Blueprint $table) {
            $table->dropColumn('production_group_id');
        });
    }
}
