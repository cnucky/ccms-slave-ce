<?php

namespace App;

use App\Utils\Master\MasterAPIRequestFactory;
use Illuminate\Database\Eloquent\Model;
use YunInternet\CCMSCommon\Constants\SlaveType;

class MasterServer extends Model
{
    public $incrementing = false;

    protected $primaryKey = "master_id";

    protected $fillable = [
        "master_id",
        "host",
        "slave_type",
        "id",
        "token",
        "last_communicate_at",
    ];

    /**
     * @return MasterAPIRequestFactory
     */
    public function makeAPIRequestFactory() : MasterAPIRequestFactory
    {
        return new MasterAPIRequestFactory($this->host, $this->id, $this->token);
    }

    /**
     * @return MasterServer
     */
    public static function getComputeNodeMaster()
    {
        return MasterServer::query()->where("slave_type", SlaveType::COMPUTE_NODE)->firstOrFail();
    }
}
