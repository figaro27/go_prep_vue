<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FlushDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flush:db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::unprepared("
            TRUNCATE `orders`;
            TRUNCATE `meal_orders`;
            TRUNCATE `meals`;
            TRUNCATE `meal_packages`;
            TRUNCATE `meal_meal_package_component_option`;
            TRUNCATE `meal_package_component_options`;
            TRUNCATE `meal_package_components`;
            TRUNCATE `meal_package_addons`;
            TRUNCATE `meal_package_sizes`;
            TRUNCATE `meal_sizes`;
            TRUNCATE `subscriptions`;
            TRUNCATE `meal_subscriptions`;
        ");
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
