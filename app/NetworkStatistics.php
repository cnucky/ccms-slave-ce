<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NetworkStatistics extends Model
{
    public $timestamps = false;

    protected $table = "network_statistics";

    protected $guarded = [];

    public function usages()
    {
        return $this->hasMany(NetworkUsage::class, "basic_network_statistics_id");
    }

    /**
     * @return self
     */
    public function preRecord()
    {
        return self::query()->where("id", $this->id - 1)->firstOrFail();
    }
}
