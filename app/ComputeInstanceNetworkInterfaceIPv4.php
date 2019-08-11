<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ComputeInstanceNetworkInterfaceIPv4 extends Model
{
    protected $table = "compute_instance_network_interface_ipv4s";

    public function networkInterface()
    {
        return $this->belongsTo(ComputeInstanceNetworkInterface::class, "network_interface_id");
    }
}