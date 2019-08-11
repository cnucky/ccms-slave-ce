<?php

namespace App\Console\Commands\ServiceConfiguration\GuestAgent;

use Illuminate\Console\Command;

class Update extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ga:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update guest agent file';

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
        $directory = "/var/www/ccms-slave-guest/";
        $filename = $directory . "index.php";
        @unlink($filename);
        if (!is_dir($directory))
            mkdir($directory);
        chmod($directory, 0750);
        chown($directory, "ccms-slave-guest");
        chgrp($directory, "ccms-slave-guest");

        link(realpath(__DIR__ . "/../../../../../public/guest/index.php"), $filename);
        chown($filename, "ccms-slave-guest");
        chgrp($filename, "ccms-slave-guest");
        chmod($filename, 0640);

        $this->info("Successfully.");
        return 0;
    }
}
