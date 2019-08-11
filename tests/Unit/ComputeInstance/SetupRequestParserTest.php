<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-17
 * Time: 上午12:17
 */

namespace Tests\Unit\ComputeInstance;


use App\Utils\ComputeInstance\SetupRequestParser;
use Tests\TestCase;
use YunInternet\CCMSCommon\Constants\NetworkInterfaceModelCode;
use YunInternet\CCMSCommon\Constants\NetworkType;

class SetupRequestParserTest extends TestCase
{
    public function testSetup()
    {
        $setupRequestParser = new SetupRequestParser([
            "unique_id" => "test_instance",
            "vCPU" => "2",
            "memory" => "2048",
            "volumes" => [
                [
                    "unique_id" => "test_volume",
                    "capacity" => 20 * 1024,
                ]
            ],
            "networkInterfaces" => [
                [
                    "type" => NetworkType::TYPE_PUBLIC_NETWORK,
                    "model" => NetworkInterfaceModelCode::MODEL_VIRTIO,
                ]
            ],
            "cdroms" => 2,
            "floppies" => 2,
        ]);

        $setupRequestParser->setup();

        $this->assertTrue(true);
    }
}