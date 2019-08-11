<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use YunInternet\CCMSCommon\Model\CompositeKey;

class CPUUsage extends Model
{
    use CompositeKey;

    public $incrementing = false;

    public $timestamps = false;

    protected $table = "cpu_usages";

    protected $primaryKey = ["basic_cpu_statistics_id", "processor_id"];

    protected $guarded = [];

    public function basicCPUStatistics()
    {
        return $this->belongsTo(CPUStatistics::class, "basic_cpu_statistics_id");
    }
}
