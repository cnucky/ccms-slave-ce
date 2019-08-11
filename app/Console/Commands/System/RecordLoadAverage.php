<?php

namespace App\Console\Commands\System;

use App\LoadAverage;
use Illuminate\Console\Command;

class RecordLoadAverage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:load-avg:record';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record system load averages';

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
        $loadAverages = sys_getloadavg();
        $now = microtime(true);
        LoadAverage::query()->create([
            "one_minute_average" => $loadAverages[0],
            "five_minutes_average" => $loadAverages[1],
            "fifteen_minutes_average" => $loadAverages[2],
            "microtime" => $now,
        ]);

        $this->info("System load averages recorded at ". $now . ".");
        return 0;
    }
}
