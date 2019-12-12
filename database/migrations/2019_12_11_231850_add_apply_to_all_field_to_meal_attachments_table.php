<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApplyToAllFieldToMealAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meal_attachments', function (Blueprint $table) {
            $table
                ->boolean('applyToAll')
                ->after('store_id')
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
        Schema::table('meal_attachments', function (Blueprint $table) {
            $table->dropColumn('applyToAll');
        });
    }
}
