<?php

namespace App;

use App\ComputeInstance\BandwidthUsage;
use App\ComputeInstance\TrafficUsage;
use Illuminate\Database\Eloquent\Model;

class ComputeInstanceNetworkInterface extends Model
{
    protected $guarded = [];

    public function instance()
    {
        return $this->belongsTo(ComputeInstance::class, "instance_id");
    }

    public function trafficUsages()
    {
        return $this->hasMany(TrafficUsage::class, "network_interface_id");
    }

    public function bandwidthUsages()
    {
        return $this->hasManyThrough(BandwidthUsage::class, TrafficUsage::class, "network_interface_id", "basic_traffic_usage_id");
    }

    public function ipv4s()
    {
        return $this->hasMany(ComputeInstanceNetworkInterfaceIPv4::class, "network_interface_id");
    }

    public function ipv6s()
    {
        return $this->hasMany(ComputeInstanceNetworkInterfaceIPv6::class, "network_interface_id");
    }
}
