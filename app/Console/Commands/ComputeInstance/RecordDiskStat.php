<?php

namespace App\Console\Commands\ComputeInstance;

use App\ComputeInstance;
use App\Utils\Libvirt\LibvirtConnection;
use App\Utils\LockFactory;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RecordDiskStat extends Command
{
    const LOCK_NAME = "ci:record-disk-stat";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ci:record-disk-stat {--calc-usage}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record compute instance disk statistics';

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
             * @var ComputeInstance\DiskStatistics[] $diskStatistics
             */
            $diskStatistics = [];
            /**
             * @var ComputeInstance $computeInstance
             */
            foreach (ComputeInstance::all() as $computeInstance) {
                try {
                    $libvirtDomain = LibvirtConnection::getConnection()->domainLookupByName($computeInstance->unique_id);
                    if ($libvirtDomain->libvirt_domain_is_active()) {
                        $domainBlockStats = $libvirtDomain->libvirt_domain_block_stats("");
                        $now = microtime(true);
                        $rdReq = $domainBlockStats["rd_req"];
                        $rdBytes = $domainBlockStats["rd_bytes"];
                        $wrReq = $domainBlockStats["wr_req"];
                        $wrBytes = $domainBlockStats["wr_bytes"];

                        $diskStatistics[] = ComputeInstance\DiskStatistics::query()->create([
                            "instance_id" => $computeInstance->id,
                            "rd_req" => $rdReq,
                            "rd_bytes" => $rdBytes,
                            "wr_req" => $wrReq,
                            "wr_bytes" => $wrBytes,
                            "microtime" => $now,
                        ]);
                    }
                } catch (\Exception $e) {
                }
            }
            if ($this->option("calc-usage")) {
                $values = [];
                foreach ($diskStatistics as $diskStatistic) {
                    try {
                        $preRecord = $diskStatistic->preRecord();
                        $calculatedValue = self::calculateUsage($preRecord, $diskStatistic);
                        $calculatedValue["basic_disk_statistics_id"] = $diskStatistic->id;
                        $values[] = $calculatedValue;
                    } catch (ModelNotFoundException $e) {
                    }
                }
                ComputeInstance\DiskUsage::query()->insert($values);
            }
            return 0;
        } finally {
            $locker
                ->unlock()
                ->clean()
            ;
        }
    }

    private static function calculateUsage(ComputeInstance\DiskStatistics $first, ComputeInstance\DiskStatistics $second)
    {
        $rdReq = $second->rd_req - $first->rd_req;
        $rdBytes = $second->rd_bytes - $first->rd_bytes;
        $wrReq = $second->wr_req - $first->wr_req;
        $wrBytes = $second->wr_bytes - $first->wr_bytes;


        if ($rdReq < 0 || $rdBytes < 0 || $wrReq < 0 || $wrBytes < 0)
            return [
                "rd_req_per_second" => 0,
                "rd_bytes_per_second" => 0,
                "wr_req_per_second" => 0,
                "wr_bytes_per_second" => 0,
            ];

        $timeDiffInSecond = $second->microtime - $first->microtime;

        return [
            "rd_req_per_second" => $rdReq / $timeDiffInSecond,
            "rd_bytes_per_second" => $rdBytes / $timeDiffInSecond,
            "wr_req_per_second" => $wrReq / $timeDiffInSecond,
            "wr_bytes_per_second" => $wrBytes / $timeDiffInSecond,
        ];
    }
}
