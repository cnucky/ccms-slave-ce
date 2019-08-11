<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use YunInternet\CCMSCommon\Model\CompositeKey;

class NetworkUsage extends Model
{
    use CompositeKey;

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = [
        "basic_network_statistics_id",
        "network_device"
    ];

    protected $guarded = [];

    public function basicNetworkStatistics()
    {
        return $this->belongsTo(NetworkStatistics::class, "basic_network_statistics_id");
    }
}
