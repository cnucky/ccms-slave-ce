<?php

namespace App\Console\Commands\ServiceConfiguration\Nginx;

use App\Utils\ServiceConfiguration\Nginx\CCMSSlaveSiteConfiguration;
use Illuminate\Console\Command;

class WriteCCMSSlaveSiteConfiguration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nginx:write-site-conf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Write CCMS-Slave site configuration.';

    private $CCMSSlaveSiteConfiguration;

    /**
     * Create a new command instance.
     *
     * @param CCMSSlaveSiteConfiguration $CCMSSlaveSiteConfiguration
     * @return void
     */
    public function __construct(CCMSSlaveSiteConfiguration $CCMSSlaveSiteConfiguration)
    {
        parent::__construct();

        $this->CCMSSlaveSiteConfiguration = $CCMSSlaveSiteConfiguration;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->CCMSSlaveSiteConfiguration->makeThenWrite();

        @unlink("/etc/nginx/sites-enabled/default");

        $this->info("Site configuration updated.");

        return 0;
    }
}
