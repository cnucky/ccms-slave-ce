<?php

namespace App\Console\Commands\System;

use App\DiskStatistics;
use App\DiskUsage;
use App\Utils\LockFactory;
use App\Utils\System\Disk;
use Illuminate\Console\Command;

class RecordDiskStat extends Command
{
    const LOCK_NAME = "system:disk:record-stat";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:disk:record-stat {--calc-usage}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record disk statistics';

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
            $data = Disk::getDiskStats();
            foreach ($data as $blockDevice => $datum) {
                $unset = false;
                if (strncmp($blockDevice, "loop", 4) === 0)
                    $unset = true;
                else if (strncmp($blockDevice, "dm-", 3) === 0)
                    $unset = true;
                else if (strncmp($blockDevice, "nvme", 4) !== 0 && preg_match('/^[a-zA-Z]{1,}[0-9]{1,}$/i', $blockDevice))
                    $unset = true;

                if ($unset)
                    unset($data[$blockDevice]);
            }

            /**
             * @var DiskStatistics $diskStat
             */
            $diskStat = DiskStatistics::query()->create([
                "data" => json_encode($data),
                "microtime" => $now,
            ]);
            $this->info("Disk statistics recorded at " . $now . ".");
            if ($this->option("calc-usage")) {
                $this->info("Calculate disk I/O usage.");
                $preRecord = $diskStat->preRecord();
                $values = [];
                foreach (Disk::calculateUsage(json_decode($preRecord->data, true), $data, $now - $preRecord->microtime) as $blockDevice => $usage) {
                    $values[] = [
                        "basic_disk_statistics_id" => $diskStat->id,
                        "block_device" => $blockDevice,
                        "read_bytes_per_second" => $usage["read_bytes_per_second"],
                        "write_bytes_per_second" => $usage["write_bytes_per_second"],
                    ];
                }
                DiskUsage::query()->insert($values);
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
