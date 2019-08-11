<?php

namespace App\Console\Commands\ServiceConfiguration\PHP;

use App\Utils\ServiceConfiguration\PHP\PHPFPMSystemdConfiguration;
use Illuminate\Console\Command;

class WritePHPFPMSystemdConfiguration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'php-fpm:write-sc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Write PHP-FPM systemd configuration';

    private $PHPFPMSystemdConfiguration;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PHPFPMSystemdConfiguration $PHPFPMSystemdConfiguration)
    {
        parent::__construct();

        $this->PHPFPMSystemdConfiguration = $PHPFPMSystemdConfiguration;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // $this->PHPFPMSystemdConfiguration->makeThenWrite();

        $this->info("PHP-FPM systemd configuration updated.");

        return 0;
    }
}
