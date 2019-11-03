<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDateRangeFieldsToCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('date_range')->default(false);
            $table->boolean('date_range_exclusive')->default(false);
            $table->timestamp('date_range_from')->nullable();
            $table->timestamp('date_range_to')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('date_range');
            $table->dropColumn('date_range_exclusive');
            $table->dropColumn('date_range_from');
            $table->dropColumn('date_range_to');
        });
    }
}
