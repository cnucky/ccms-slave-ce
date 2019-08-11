<?php

namespace App\Console\Commands\Permission;

use Illuminate\Console\Command;

class Reset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'perm:rst';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset ccms-slave file permission';

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
        $rootDirectory = realpath(__DIR__ . "/../../../../");
        if ($rootDirectory === false) {
            $this->error("$rootDirectory not found.");
            return 1;
        }

        if (!file_exists($rootDirectory . "/artisan")) {
            $this->error("$rootDirectory is not ccms-slave directory.");
            return 1;
        }

        $owner = "ccms-slave";

        system("chmod -R 0700 $rootDirectory");
        system("chmod 0710 $rootDirectory $rootDirectory/public $rootDirectory/noVNC");
        system("chmod 0640 $rootDirectory/public/index.php");
        system("chmod -R 0750 $rootDirectory/noVNC/*");
        system("chmod -R 0640 $rootDirectory/noVNC/certificate/*");
        /*
        system("chown -R root:root $rootDirectory");
        system("chown root:www-data $rootDirectory $rootDirectory/public $rootDirectory/public/index.php");
        */
        system("chown -R $owner:$owner $rootDirectory");
        // system("chown $owner:$owner $rootDirectory $rootDirectory/public $rootDirectory/public/index.php");

        $directory = "/var/www/ccms-slave-guest/";
        $filename = $directory . "index.php";
        chown($filename, "ccms-slave-guest");
        chgrp($filename, "ccms-slave-guest");
        chmod($filename, 0640);
        return 0;
    }
}
