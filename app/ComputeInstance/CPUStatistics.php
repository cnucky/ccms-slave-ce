<?php

namespace App\ComputeInstance;

use Illuminate\Database\Eloquent\Model;

class CPUStatistics extends Model
{
    public $timestamps = false;

    protected $table = "compute_instance_cpu_statistics";

    protected $guarded = [];

    public function usages()
    {
        return $this->hasMany(CPUUsage::class, "basic_cpu_statistics_id");
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
