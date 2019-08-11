<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use YunInternet\CCMSCommon\Model\CompositeKey;

class DiskUsage extends Model
{
    use CompositeKey;

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = [
        "basic_disk_statistics_id",
        "block_device",
    ];

    protected $guarded = [];

    public function basicDiskStatistics()
    {
        return $this->belongsTo(DiskStatistics::class, "basic_disk_statistics_id");
    }
}
