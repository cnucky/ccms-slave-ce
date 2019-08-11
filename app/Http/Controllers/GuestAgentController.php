<?php

namespace App\Http\Controllers;

use App\ComputeInstance;
use App\ComputeInstanceNetworkInterface;
use App\Utils\InstanceOSConfigure\Loader;
use App\Utils\Libvirt\LibvirtConnection;
use Illuminate\Http\Request;
use YunInternet\CCMSCommon\Constants\Constants;
use YunInternet\Libvirt\Exception\LibvirtException;
use YunInternet\Libvirt\GuestAgent;

class GuestAgentController extends Controller
{
    public function fromHostOnly(Request $request, Loader $loader)
    {
        $leases = $this->getHostOnlyNetwork()->libvirt_network_get_dhcp_leases();
        $lease = $this->searchLeaseRecordByIP($request->ip, $leases);
        if ($lease === false)
            return ["result" => false, "message" => "Lease record not found"];

        $hostOnlyMac = $lease["mac"];

        /**
         * @var ComputeInstance $computeInstance
         */
        $computeInstance = ComputeInstance::query()->where("host_only_nic_mac_address", $hostOnlyMac)->firstOrFail();

        $firstBoot = $computeInstance->first_boot;
        if (!$firstBoot)
            return ["result" => true, "message" => "No action need"];

        $configurator = Loader::newInstance($request->os, $computeInstance);
        $configurator->firstBoot();
        $computeInstance->update(["first_boot" => 0, "os" => $request->os]);
        return ["result" => true, "mac" => $hostOnlyMac, "unique_id" => $computeInstance->unique_id];
    }


    private function getHostOnlyNetwork()
    {
        return LibvirtConnection::getConnection()->networkGet(Constants::DEFAULT_HOST_ONLY_NETWORK_NAME);
    }

    private function searchLeaseRecordByIP($ip, &$leases)
    {
        foreach ($leases as $lease) {
            if ($ip === $lease["ipaddr"])
                return $lease;
        }
        return false;
    }
}
