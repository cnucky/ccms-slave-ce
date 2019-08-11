<?php

namespace App\Console\Commands\ServiceConfiguration\Rsync;

use App\Utils\ServiceConfiguration\Rsync\RsyncInitScriptDefault;
use Illuminate\Console\Command;

class WriteInitScriptDefault extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rsync:write-isd';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Write rsync init script default';

    private $rsyncInitScriptDefault;

    /**
     * Create a new command instance.
     *
     * @param RsyncInitScriptDefault $rsyncInitScriptDefault
     * @return void
     */
    public function __construct(RsyncInitScriptDefault $rsyncInitScriptDefault)
    {
        parent::__construct();

        $this->rsyncInitScriptDefault = $rsyncInitScriptDefault;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->rsyncInitScriptDefault->makeThenWrite();
        $this->info("Rsync init script default updated.");
        return 0;
    }
}
