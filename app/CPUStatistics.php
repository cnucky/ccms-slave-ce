<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CPUStatistics extends Model
{
    public $timestamps = false;

    protected $table = "cpu_statistics";

    protected $guarded = [];

    public function usages()
    {
        return $this->hasMany(CPUUsage::class, "basic_cpu_statistics_id");
    }

    /**
     * @return self
     */
    public function preRecord()
    {
        return self::query()->where("id", $this->id - 1)->firstOrFail();
    }
}
