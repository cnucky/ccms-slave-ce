<?php

namespace App\Console\Commands\ServiceConfiguration\Rsync;

use App\Utils\ServiceConfiguration\Rsync\RsyncConfiguration;
use Illuminate\Console\Command;

class WriteConfiguration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rsync:write-conf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Write rsync configuration.';

    private $rsyncConfiguration;

    /**
     * Create a new command instance.
     *
     * @param RsyncConfiguration $rsyncConfiguration
     * @return void
     */
    public function __construct(RsyncConfiguration $rsyncConfiguration)
    {
        parent::__construct();

        $this->rsyncConfiguration = $rsyncConfiguration;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->rsyncConfiguration->makeThenWrite();
        $this->info("Rsync configuration updated.");
        return 0;
    }
}
