<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DiskStatistics extends Model
{
    public $timestamps = false;

    protected $table = "disk_statistics";

    protected $guarded = [];

    public function usages()
    {
        return $this->hasMany(DiskUsage::class, "basic_disk_statistics_id");
    }

    /**
     * @return self
     */
    public function preRecord()
    {
        return self::query()->where("id", $this->id - 1)->firstOrFail();
    }
}
