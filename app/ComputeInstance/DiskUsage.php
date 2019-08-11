<?php

namespace App\ComputeInstance;

use Illuminate\Database\Eloquent\Model;

class DiskUsage extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $table = "compute_instance_disk_usages";

    protected $primaryKey = "basic_disk_statistics_id";

    protected $guarded = [];

    public function basicDiskStatistics()
    {
        return $this->belongsTo(DiskStatistics::class, "basic_disk_statistics_id");
    }
}
