<?php

namespace App\Http\Controllers;

use App\Constants\AvailableSystemConfigurations;
use App\SystemConfigurations;
use Illuminate\Http\Request;

class CCMSSlaveController extends Controller
{
    public function nodeUUID()
    {
        $uuid = SystemConfigurations::value(AvailableSystemConfigurations::CCMS_SLAVE_UUID);
        if (empty($uuid))
            return ["result" => false, "message" => "UUID not found"];
        return ["result" => true, "uuid" => $uuid];
    }
}
