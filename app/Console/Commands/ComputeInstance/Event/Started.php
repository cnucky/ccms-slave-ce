<?php

namespace App\Console\Commands\ComputeInstance\Event;

use App\MasterServer;
use Illuminate\Console\Command;

class Started extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ci:event:started {uniqueId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compute instance started event';

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
        var_dump(MasterServer::getComputeNodeMaster()->makeAPIRequestFactory()->make("/computeInstances/" . $this->argument("uniqueId") . "/started")->withPostFields([])->JSONResponse());
        return 0;
    }
}
