<?php

namespace App\Console\Commands\ServiceConfiguration\PHP;

use App\Utils\ServiceConfiguration\PHP\CCMSSlavePHPFPMPoolConfiguration;
use Illuminate\Console\Command;

class WriteCCMSSlavePHPFPMPoolConfiguration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'php-fpm:write-pool-conf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Write PHP-FPM pool configuration';

    private $CCMSSlavePHPFPMPoolConfiguration;

    /**
     * Create a new command instance.
     *
     * @var CCMSSlavePHPFPMPoolConfiguration $CCMSSlavePHPFPMPoolConfiguration
     * @return void
     */
    public function __construct(CCMSSlavePHPFPMPoolConfiguration $CCMSSlavePHPFPMPoolConfiguration)
    {
        parent::__construct();

        $this->CCMSSlavePHPFPMPoolConfiguration = $CCMSSlavePHPFPMPoolConfiguration;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->CCMSSlavePHPFPMPoolConfiguration->makeThenWrite();

        $this->info("CCMS-Slave PHP-FPM pool configuration updated.");

        return 0;
    }
}
