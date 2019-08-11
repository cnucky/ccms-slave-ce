<?php

namespace App\ComputeInstance;

use App\ComputeInstanceNetworkInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TrafficUsage extends Model
{
    public $timestamps = false;

    protected $table = "compute_instance_traffic_usages";

    protected $guarded = [];

    public function networkInterface()
    {
        return $this->belongsTo(ComputeInstanceNetworkInterface::class, "network_interface_id");
    }

    public function bandwidthUsage()
    {
        return $this->hasOne(BandwidthUsage::class, "basic_traffic_usage_id");
    }

    public function calculateBandwidthUsage()
    {
        return BandwidthUsage::createFromTrafficUsage($this);
    }

    /**
     * @return self
     * @throws ModelNotFoundException
     */
    public function preTrafficUsage()
    {
        return self::query()
            ->where("id", "<", $this->id)
            ->where("network_interface_id", $this->network_interface_id)
            ->orderByDesc("id")
            ->limit(1)
            ->firstOrFail()
            ;
    }
}
