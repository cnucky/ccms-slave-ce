<?php

namespace App\Console\Commands;

use App\Constants\AvailableSystemConfigurations;
use App\SystemConfigurations;
use Illuminate\Console\Command;

class EnableImageServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image:enable-server';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable image server';

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
        SystemConfigurations::setValue(AvailableSystemConfigurations::IMAGE_SERVER, 1);
        $this->info("Set public image secret.");
        $this->call("rsync:set-public-image-secret", ["--no-update-secret-file" => true]);
        $this->call("rsync:write-secrets");
        $this->call("rsync:write-isd");
        $this->call("rsync:write-conf");

        system("/usr/bin/sudo /etc/init.d/rsync restart");
        return 0;
    }
}
