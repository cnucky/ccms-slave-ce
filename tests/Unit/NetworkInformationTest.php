<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-11
 * Time: 下午11:27
 */

namespace Tests\Unit;


use App\Utils\System\Network;
use Tests\TestCase;

class NetworkInformationTest extends TestCase
{
    public function testNetworkBandwidthUsage()
    {
        var_dump(Network::currentBandwidthUsage());

        $this->assertTrue(true);
    }
}