<?php

namespace App\Console\Commands;

use App\StoreDetail;
use Illuminate\Console\Command;

class MigrateLogos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:logos';

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
        $details = StoreDetail::all();

        foreach ($details as $detail) {
            $logo = $detail->getMedia('logo')->first();
            $logoOrig = $detail->getOriginal('logo');

            if ($logoOrig && !$logo) {
                try {
                    $fullImagePath =
                        $logoOrig === '/images/store/store-logo.jpg'
                            ? resource_path('assets/' . $logoOrig)
                            : $logoOrig;

                    $detail->clearMediaCollection('logo');
                    $this->comment('Migrating ' . $fullImagePath);
                    $detail
                        ->addMedia($fullImagePath)
                        ->preservingOriginal()
                        ->toMediaCollection('logo');
                    $detail->save();
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
        }
    }
}
