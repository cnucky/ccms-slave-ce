<?php
/**
 * Created by PhpStorm.
 * Date: 19-3-22
 * Time: 上午3:45
 */

namespace Tests\Unit\Libvirt\Network;


use App\Utils\Libvirt\LibvirtConnection;
use Tests\TestCase;
use YunInternet\CCMSCommon\Constants\Constants;

class NetworkTest extends TestCase
{
    public function testNetworkGetDHCPLeases()
    {
        var_dump(LibvirtConnection::getConnection()->networkGet(Constants::DEFAULT_HOST_ONLY_NETWORK_NAME)->libvirt_network_get_dhcp_leases());
        $this->assertTrue(true);
    }
}