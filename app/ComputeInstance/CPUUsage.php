<?php

namespace App\ComputeInstance;

use Illuminate\Database\Eloquent\Model;

class CPUUsage extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $table = "compute_instance_cpu_usages";

    protected $primaryKey = "basic_cpu_statistics_id";

    protected $guarded = [];

    public function basicCPUStatistics()
    {
        return $this->belongsTo(CPUStatistics::class, "basic_cpu_statistics_id");
    }
}
