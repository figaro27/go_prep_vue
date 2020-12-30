<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DbDump extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:dump {path?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the mysqldump utility using info from .env';

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
        $ds = DIRECTORY_SEPARATOR;
        $ts = time();

        $host = env('DB_HOST');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $database = env('DB_DATABASE');

        $defaultDir =
            database_path() .
            $ds .
            'backups' .
            $ds .
            date('Y', $ts) .
            $ds .
            date('m', $ts) .
            $ds .
            date('d', $ts) .
            $ds;
        $defaultFilename =
            date('Y-m-d-His', $ts) . '-dump-' . $database . '.sql';

        if ($this->hasArgument('path')) {
            $path = $this->argument('path');
        } else {
            $path = $defaultDir . $defaultFilename;
        }

        $command = sprintf(
            'mysqldump -h %s -u %s%s %s > %s',
            $host,
            $username,
            empty($password) ? '' : sprintf(' -p\'%s\'', $password),
            $database,
            $path
        );

        $pathInfo = pathinfo($path);
        $dir = $pathInfo['dirname'];

        if (!is_dir($dir) && $dir !== '.') {
            mkdir($dir, 0755, true);
        }

        exec($command);
    }
}
