<?php

namespace App\Http\Controllers\MasterServer;

use App\MasterServer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use YunInternet\CCMSCommon\Constants\SlaveType;

class Register extends Controller
{
    public function __invoke(Request $request)
    {
        $this->validate($request, [
            "masterId" => "required",
            "host" => "required",
            "slaveType" => ["required", "integer", Rule::in([
                SlaveType::COMPUTE_NODE,
                SlaveType::IMAGE_NODE,
            ])],
            "id" => "required|integer",
            "token" => "nullable",
        ]);

        $masterServer = MasterServer::query()->updateOrCreate([
            "master_id" => $request->masterId,
            "slave_type" => $request->slaveType
        ], [
            "host" => $request->host,
            "id" => $request->id,
        ]);

        if (@strlen(@$request->token))
            $masterServer->token = $request->token;
        $masterServer->save();

        return ["result" => true];
    }
}
