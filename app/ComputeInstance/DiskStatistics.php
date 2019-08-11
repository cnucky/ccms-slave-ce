<?php

namespace App\ComputeInstance;

use Illuminate\Database\Eloquent\Model;

class DiskStatistics extends Model
{
    public $timestamps = false;

    protected $table = "compute_instance_disk_statistics";

    protected $guarded = [];

    public function usages()
    {
        return $this->hasMany(DiskUsage::class, "basic_disk_statistics_id");
    }

    public function preRecord()
    {
        return self::query()
            ->where("id", "<", $this->id)
            ->where("instance_id", $this->instance_id)
            ->orderByDesc("id")
            ->limit(1)
            ->firstOrFail()
            ;
    }
}
