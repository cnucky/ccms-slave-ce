<?php

namespace App\Console\Commands\ComputeInstance\Monitor;

use App\ComputeInstance\CPUStatistics;
use App\ComputeInstance\DiskStatistics;
use Illuminate\Console\Command;

class Clean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ci:monitor:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean useless history monitor data';

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
        $maxTime = time() - 3600;

        $deletedCount = $this->commonWhere(CPUStatistics::query(), $maxTime)->delete();
        $this->info("$deletedCount cpu statistics record deleted.");

        $deletedCount = $this->commonWhere(DiskStatistics::query(), $maxTime)->delete();
        $this->info("$deletedCount disk statistics record deleted.");
        return 0;
    }

    private function commonWhere($builder, $maxTime, $relationName = "usages")
    {
        $builder
            ->whereDoesntHave("usages")
            ->where("microtime", "<=", $maxTime)
        ;
        return $builder;
    }
}
