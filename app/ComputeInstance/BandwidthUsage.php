<?php

namespace App\ComputeInstance;

use App\ComputeInstanceNetworkInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BandwidthUsage extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $table = "compute_instance_bandwidth_usages";

    protected $primaryKey = "basic_traffic_usage_id";

    protected $guarded = [];

    public function networkInterface()
    {
        return $this->belongsTo(ComputeInstanceNetworkInterface::class);
    }

    public function basicTrafficUsage()
    {
        return $this->belongsTo(TrafficUsage::class, "basic_traffic_usage_id");
    }

    /**
     * @param int|TrafficUsage $trafficUsage
     * @param null|double $timeDiffInSecond
     * @return self
     * @throws ModelNotFoundException
     */
    public static function createFromTrafficUsage($trafficUsage, $timeDiffInSecond = null)
    {
        /**
         * @var TrafficUsage $secondTrafficUsage
         */
        $secondTrafficUsage = $trafficUsage;
        if (!($secondTrafficUsage instanceof TrafficUsage))
            $secondTrafficUsage = TrafficUsage::query()->findOrFail($secondTrafficUsage);

        if (is_null($timeDiffInSecond)) {
            $firstTrafficUsage = $secondTrafficUsage->preTrafficUsage();
            $timeDiffInSecond = $secondTrafficUsage->microtime - $firstTrafficUsage->microtime;
        }

        if ($timeDiffInSecond === 0) {
            $rxBytesPerSecond = 0;
            $rxPacketsPerSecond = 0;
            $txBytesPerSecond = 0;
            $txPacketsPerSecond = 0;
        } else {
            $rxBytesPerSecond = max(0, $secondTrafficUsage->rx_byte_count / $timeDiffInSecond);
            $rxPacketsPerSecond = max(0, $secondTrafficUsage->rx_packet_count / $timeDiffInSecond);
            $txBytesPerSecond = max(0, $secondTrafficUsage->tx_byte_count / $timeDiffInSecond);
            $txPacketsPerSecond = max(0, $secondTrafficUsage->tx_packet_count / $timeDiffInSecond);
        }

        return BandwidthUsage::query()->create([
            "basic_traffic_usage_id" => $secondTrafficUsage->id,
            "rx_bytes_per_second" => $rxBytesPerSecond,
            "rx_packets_per_second" => $rxPacketsPerSecond,
            "tx_bytes_per_second" => $txBytesPerSecond,
            "tx_packets_per_second" => $txPacketsPerSecond,
        ]);
    }
}
