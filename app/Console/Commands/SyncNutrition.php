<?php

namespace App\Console\Commands;

use App\Ingredient;
use Illuminate\Console\Command;
use App\Http\Controllers\NutritionController;
use Illuminate\Http\Request;

class SyncNutrition extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:nutrition';

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
        $controller = new NutritionController();

        $grouped = Ingredient::all()->groupBy('unit_type');

        $precision = 1000000;
        $typeMap = [
            'mass' => 'g',
            'volume' => 'ml',
            'unit' => ''
        ];

        foreach ($grouped as $unitType => $ingredients) {
            $unit = $typeMap[$unitType];

            $ingredients = $ingredients->pluck('food_name')->unique();

            foreach ($ingredients->chunk(100) as $chunk) {
                $query = $chunk
                    ->map(function ($ingredient) use ($unit, $precision) {
                        return $precision . $unit . ' ' . $ingredient;
                    })
                    ->implode(', ');

                $this->info($query);

                $request = new Request();
                $request->request->add([
                    'query' => $query
                ]);

                $nutrition = $controller->getNutrients($request);

                foreach ($nutrition['foods'] as $food) {
                    $newVals = [];

                    foreach (['sugars', 'addedSugars', 'totalcarb'] as $field) {
                        $newVals[$field] = $food->get($field, 0) / $precision;
                    }

                    Ingredient::where(
                        'food_name',
                        $food->get('food_name')
                    )->update($newVals);
                }

                sleep(3);
            }
        }
    }
}
