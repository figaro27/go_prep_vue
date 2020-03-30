<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\User;

class SeedReferralUrlCodesOnUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $users = User::all();
        foreach ($users as $user) {
            $user->referralUrlCode =
                'R' .
                strtoupper(substr(uniqid(rand(10, 99), false), -4)) .
                chr(rand(65, 90)) .
                rand(0, 9) .
                rand(0, 9) .
                chr(rand(65, 90));
            $user->update();
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
