<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\MealSize;

class updateMealSizeFullTitles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goprep:updateMealSizeFullTitles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Changing full_title attribute to column on meal sizes table. This command fills in the new column.';

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
        $mealSizes = MealSize::all();

        foreach ($mealSizes as $mealSize) {
            try {
                $mealSize->full_title = $mealSize->meal
                    ? $mealSize->meal->title . ' - ' . $mealSize->title
                    : 'Deleted - ' . $mealSize->title;
                $mealSize->update();
            } catch (\Exception $e) {
            }
        }
    }
}
