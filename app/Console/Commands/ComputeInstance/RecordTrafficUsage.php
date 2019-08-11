<?php

namespace App\Console\Commands\ComputeInstance;

use App\ComputeInstance;
use App\ComputeInstance\TrafficUsage;
use App\ComputeInstanceNetworkInterface;
use App\Utils\LockFactory;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class RecordTrafficUsage extends Command
{
    const LOCK_NAME = "ci:record-traffic-usage";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ci:record-traffic-usage {--calc-bandwidth-usage}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record compute instance traffic usage';

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
            exec("/usr/bin/sudo /sbin/ebtables -t nat -L --Lc", $output, $return_var);
            $now = microtime(true);
            exec("/usr/bin/sudo /sbin/ebtables -t nat -Z");

            $values = $this->parseEbtablesOutput($output);

            /**
             * @var TrafficUsage[] $trafficUsages
             */
            $trafficUsages = [];

            foreach ($values as $mac => $value) {
                try {
                    $networkInterfaceId = ComputeInstanceNetworkInterface::query()->where("mac", $mac)->firstOrFail()->id;
                    $value["network_interface_id"] = $networkInterfaceId;
                    $value["microtime"] = $now;

                    $trafficUsages[] = TrafficUsage::query()->create($value);
                } catch (\Exception $e) {
                    $this->error($e->getFile() . ":" . $e->getLine() . " " .$e->getMessage());
                    Log::error($e->getMessage());
                }
            }

            if ($this->option("calc-bandwidth-usage")) {
                foreach ($trafficUsages as $trafficUsage) {
                    try {
                        $trafficUsage->calculateBandwidthUsage();
                    } catch (ModelNotFoundException $e) {
                    }
                }
            }
        } finally {
            $locker
                ->unlock()
                ->clean()
            ;
        }
        END_COMMAND:
        return 0;
    }

    private function parseEbtablesOutput($output)
    {
        $values = [];

        foreach ($output as $row) {
            try {
                if (preg_match('/^-(d|s) (52:54:0:([a-fA-F0-9]{1,2}:){2,2}[a-fA-F0-9]{1,2}) -j RETURN , pcnt = ([0-9]+) -- bcnt = ([0-9]+)$/', $row, $matches)) {
                    $direction = $matches[1];
                    $mac = preg_replace("/:([a-fA-F0-9])(:| )/", ':0$1$2', $matches[2]);
                    $packetCount = $matches[4];
                    $byteCount = $matches[5];
                    // $this->line(sprintf("Direction: %s, mac: %s, packet count: %s, byte count: %s", $direction, $mac, $packetCount, $byteCount));

                    $prefix = "rx";
                    if ($direction === "s") {
                        $prefix = "tx";
                    }

                    $values[$mac][$prefix . "_byte_count"] = $byteCount;
                    $values[$mac][$prefix . "_packet_count"] = $packetCount;
                }
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                Log::error($e->getMessage());
            }
        }

        return $values;
    }
}
