<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use App\Store;
use App\StorePlan;

class StorePlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plans:manage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var \App\Store
     */
    protected $store;

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
        while (1) {
            $storeId = $this->ask('Enter store ID');
            $this->store = Store::with(['user'])->find($storeId);

            if (
                $this->confirm(
                    'Is this the right store? ' . $this->store->user->email,
                    true
                )
            ) {
                break;
            }
        }

        while (1) {
            $commands = collect([
                'l' => 'List current plan',
                'c' => 'Create plan',
                //'u' => 'Update plan',
                'q' => 'Quit'
            ]);
            $command = $this->choice(
                'What would you like to do?',
                $commands->toArray()
            );

            switch ($command) {
                case 'l':
                    $this->info($this->store->plan ?? 'This store has no plan');
                    break;

                case 'c':
                    $this->create();
                    break;

                case 'u':
                    //$this->update();
                    break;

                case 'q':
                    return;

                default:
                    continue;
            }
        }
    }

    protected function create()
    {
        $price = null;

        while (!is_numeric($price)) {
            $price = $this->ask(
                'What price, in cents? i.e. Enter $10.00 as 1000'
            );
        }

        $period = $this->choice('What period?', [
            'm' => 'Monthly',
            'w' => 'Weekly'
        ]);

        $period = [
            'm' => 'month',
            'w' => 'week'
        ][$period];

        $day = null;
        while (!is_numeric($day)) {
            $periodQuestion = [
                'month' => 'Enter day of the month to charge (1 - 31)',
                'week' =>
                    'Enter day of the week to charge (1 = Monday, 7 = Sunday)'
            ][$period];

            $day = $this->ask($periodQuestion);
        }

        StorePlan::create($this->store, $price, $period, $day);

        $this->store->refresh();

        $this->info('Plan created!');
    }
}
