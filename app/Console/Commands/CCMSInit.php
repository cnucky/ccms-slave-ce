<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CCMSInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccms:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Init ccms-slave';

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
        $databaseConfigurations = DB::getConfig();
        if ($databaseConfigurations["driver"] === "sqlite") {
            $databaseFilePath = $databaseConfigurations["database"];

            if (!file_exists($databaseFilePath)) {
                $this->info(sprintf("Creating database file %s", $databaseFilePath));
                touch($databaseConfigurations["database"]);
            }

            $this->info("Set database file permission");
            chown($databaseFilePath, "root");
            chgrp($databaseFilePath, "root");
            chmod($databaseFilePath, 0700);
        }

        return 0;
    }
}
