<?php

namespace App\Console\Commands\ServiceConfiguration\Rsync;

use App\Utils\ServiceConfiguration\Rsync\RsyncSecret;
use Illuminate\Console\Command;

class WriteSecrets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rsync:write-secrets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Write rsync secrets.';

    private $rsyncSecret;

    /**
     * Create a new command instance.
     *
     * @param RsyncSecret $rsyncSecret
     * @return void
     */
    public function __construct(RsyncSecret $rsyncSecret)
    {
        parent::__construct();

        $this->rsyncSecret = $rsyncSecret;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->rsyncSecret->makeThenWrite();
        $this->info("Rsync secret file updated.");
        return 0;
    }
}
