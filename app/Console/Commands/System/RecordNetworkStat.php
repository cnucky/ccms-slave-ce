<?php

namespace App\Console\Commands\System;

use App\NetworkStatistics;
use App\NetworkUsage;
use App\Utils\LockFactory;
use App\Utils\System\Network;
use Illuminate\Console\Command;

class RecordNetworkStat extends Command
{
    const LOCK_NAME = "system:network:record-stat";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:network:record-stat {--calc-usage}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record network statistics';

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
            $statData = Network::formatNetDeviceStatus();
            foreach ($statData as $nic => $datum) {
                $unset = false;
                if ($nic === "lo") {
                    $unset = true;
                } else if (strncmp($nic, "vnet", 4) === 0) {
                    $unset = true;
                } else if (strncmp($nic, "virbr", 5) === 0) {
                    $unset = true;
                } else if (strncmp($nic, "veth", 4) === 0) {
                    $unset = true;
                }  else if (strncmp($nic, "ccms-", 5) === 0) {
                    $unset = true;
                }  else if (strncmp($nic, "docker", 6) === 0) {
                    $unset = true;
                }

                if ($unset) {
                    unset($statData[$nic]);
                }
            }

            /**
             * @var NetworkStatistics $networkStat
             */
            $networkStat = NetworkStatistics::query()->create([
                "data" => json_encode($statData),
                "microtime" => $now,
            ]);
            $this->info("Network statistics recorded at " . $now . ".");
            if ($this->option("calc-usage")) {
                $this->info("Calculate bandwidth usage.");
                $preRecord = $networkStat->preRecord();
                $values = [];
                foreach (Network::calculateUsage(json_decode($preRecord->data, true), $statData, $now - $preRecord->microtime) as $networkDevice => $usage) {
                    $values[] = [
                        "basic_network_statistics_id" => $networkStat->id,
                        "network_device" => $networkDevice,
                        "rx_bytes_per_second" => $usage["rx_bytes_per_second"],
                        "rx_packets_per_second" => $usage["rx_packets_per_second"],
                        "tx_bytes_per_second" => $usage["tx_bytes_per_second"],
                        "tx_packets_per_second" => $usage["tx_packets_per_second"],
                    ];
                }

                NetworkUsage::query()->insert($values);
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
