<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ComputeInstance extends Model
{
    protected $guarded = [];

    public function configurationLogs()
    {
        return $this->hasMany(ComputeInstanceConfigurationLog::class, "unique_id", "unique_id");
    }

    public function networkInterfaces()
    {
        return $this->hasMany(ComputeInstanceNetworkInterface::class, "instance_id");
    }

    public function cpuUsages()
    {
        return $this->hasManyThrough(\App\ComputeInstance\CPUUsage::class, \App\ComputeInstance\CPUStatistics::class, "instance_id", "basic_cpu_statistics_id");
    }

    public function diskUsages()
    {
        return $this->hasManyThrough(\App\ComputeInstance\DiskUsage::class, \App\ComputeInstance\DiskStatistics::class, "instance_id", "basic_disk_statistics_id");
    }
}
