<?php

namespace App\Console\Commands\System;

use App\CPUStatistics;
use App\CPUUsage;
use App\Utils\LockFactory;
use App\Utils\System\CPU;
use Illuminate\Console\Command;

class RecordCPUStat extends Command
{
    const LOCK_NAME = "system:cpu:record-stat";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:cpu:record-stat {--calc-usage}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record CPU statistics';

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
        $locker = LockFactory::getLocker(self::LOCK_NAME);
        $locker->exclusive(true);

        try {
            $now = microtime(true);
            $statData = CPU::getCPUStatistics();
            /**
             * @var CPUStatistics $cpuStat
             */
            $cpuStat = CPUStatistics::query()->create([
                "data" => json_encode($statData),
                "microtime" => $now,
            ]);
            $this->info("CPU statistics recorded at " . $now . ".");
            if ($this->option("calc-usage")) {
                $this->info("Calculate CPU usage.");
                $preRecord = $cpuStat->preRecord();
                $values = [];
                foreach (CPU::calculateUsage(json_decode($preRecord->data, true), $statData) as $processor => $usage) {
                    $values[] = [
                        "basic_cpu_statistics_id" => $cpuStat->id,
                        "processor" => $processor,
                        "user" => $usage["user"],
                        "nice" => $usage["nice"],
                        "system" => $usage["system"],
                        "idle" => $usage["idle"],
                        "iowait" => $usage["iowait"],
                        "irq" => $usage["irq"],
                        "softirq" => $usage["softirq"],
                        "steal" => $usage["steal"],
                        "guest" => $usage["guest"],
                        "guest_nice" => $usage["guest_nice"],
                    ];
                }

                CPUUsage::query()->insert($values);
            }
            return 0;
        } finally {
            $locker
                ->unlock()
                ->clean()
            ;
        }
    }
}
