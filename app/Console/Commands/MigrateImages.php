<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Meal;

class MigrateImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:images';

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
        $meals = Meal::all();

        foreach ($meals as $meal) {
            $mediaItems = $meal->getMedia('featured_image');

            if ($meal->featured_image && !count($mediaItems)) {
                try {
                    $fullImagePath = resource_path(
                        'assets/' . $meal->featured_image
                    );
                    $meal->clearMediaCollection('featured_image');
                    $this->comment('Migrating ' . $fullImagePath);
                    $meal
                        ->addMedia($fullImagePath)
                        ->preservingOriginal()
                        ->toMediaCollection('featured_image');
                    $meal->save();
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
        }
    }
}
