<?php

namespace App\Console\Commands\ComputeInstance;

use App\ComputeInstance;
use App\Utils\Libvirt\LibvirtConnection;
use App\Utils\LockFactory;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RecordCPUStat extends Command
{
    const LOCK_NAME = "ci:record-cpu-stat";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ci:record-cpu-stat {--calc-usage}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record compute instance cpu statistics';

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
            /**
             * @var ComputeInstance\CPUStatistics[] $cpuStatistics
             */
            $cpuStatistics = [];
            /**
             * @var ComputeInstance $computeInstance
             */
            foreach (ComputeInstance::all() as $computeInstance) {
                try {
                    $libvirtDomain = LibvirtConnection::getConnection()->domainLookupByName($computeInstance->unique_id);
                    if ($libvirtDomain->libvirt_domain_is_active()) {
                        $cpuStat = $libvirtDomain->libvirt_domain_get_cpu_total_stats();
                        $now = microtime(true);
                        $cpuStatistics[] = ComputeInstance\CPUStatistics::query()->create([
                            "instance_id" => $computeInstance->id,
                            "cpu_time" => $cpuStat["cpu_time"],
                            "user_time" => $cpuStat["user_time"],
                            "system_time" => $cpuStat["system_time"],
                            "microtime" => $now,
                        ]);
                    }
                } catch (\Exception $exception) {
                }
            }
            if ($this->option("calc-usage")) {
                $values = [];
                foreach ($cpuStatistics as $cpuStatistic) {
                    try {
                        $calculatedValue = self::calculateUsage($cpuStatistic->preRecord(), $cpuStatistic);
                        $calculatedValue["basic_cpu_statistics_id"] = $cpuStatistic->id;
                        $values[] = $calculatedValue;
                    } catch (ModelNotFoundException $e) {
                    }
                }
                ComputeInstance\CPUUsage::query()->insert($values);
            }
            return 0;
        } finally {
            $locker
                ->unlock()
                ->clean()
            ;
        }
    }

    private static function calculateUsage(ComputeInstance\CPUStatistics $first, ComputeInstance\CPUStatistics $second)
    {
        $cpuTime = $second->cpu_time - $first->cpu_time;
        $user_time = $second->user_time - $first->user_time;
        $system_time = $second->system_time - $first->system_time;

        if ($cpuTime < 0 || $user_time < 0 || $system_time < 0) {
            return [
                "cpu_usage" => 0,
                "user_usage" => 0,
                "system_usage" => 0,
            ];
        }

        $timeDiffInSecond = $second->microtime - $first->microtime;
        return [
            "cpu_usage" => $cpuTime / $timeDiffInSecond * 100,
            "user_usage" => $user_time / $timeDiffInSecond * 100,
            "system_usage" => $system_time / $timeDiffInSecond * 100,
        ];
    }
}
