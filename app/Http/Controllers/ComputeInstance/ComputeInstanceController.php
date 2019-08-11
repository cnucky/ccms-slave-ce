<?php

namespace App\Http\Controllers\ComputeInstance;

use App\ComputeInstance;
use App\ComputeInstanceConfigurationLog;
use App\ComputeInstanceNetworkInterface;
use App\ComputeInstanceResource;
use App\Constants\ComputeInstance\StatusCode;
use App\Utils\ComputeInstance\NetworkInterfaces;
use App\Utils\ComputeInstance\SetupRequestParser;
use App\Utils\ComputeInstanceUtils;
use App\Utils\InstanceOSConfigure\Loader;
use App\Utils\Libvirt\LibvirtConnection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use YunInternet\CCMSCommon\Constants\NetworkType;
use YunInternet\CCMSCommon\Utils\DomainXML;
use YunInternet\Libvirt\Constants\Domain\VirDomainXMLFlags;

class ComputeInstanceController extends Controller
{
    public function setup(Request $request)
    {
        if ($computeInstance = ComputeInstance::query()->where("unique_id", $request->unique_id)->first()) {
            $domain = LibvirtConnection::getConnection()->domainLookupByName($request->unique_id);
            $domainXML = $domain->libvirt_domain_get_xml_desc(null, VirDomainXMLFlags::VIR_DOMAIN_XML_INACTIVE | VirDomainXMLFlags::VIR_DOMAIN_XML_SECURE);

            $domainXMLElement = new \SimpleXMLElement($domainXML);
            $uuid = $domainXMLElement->uuid->__toString();
            $macAddresses = DomainXML::findMacAddresses($domainXMLElement);
        } else {
            $configuration = $request->all();
            unset($configuration["host_only_nic_mac_address"]);

            $setupRequestParser = new SetupRequestParser($configuration);
            ComputeInstanceConfigurationLog::query()->create([
                "unique_id" => $request->unique_id,
                "configuration" => $request->getContent(),
            ]);

            try {
                $domain = $setupRequestParser->setup();
                $domainXML = $domain->libvirt_domain_get_xml_desc(null, VirDomainXMLFlags::VIR_DOMAIN_XML_INACTIVE | VirDomainXMLFlags::VIR_DOMAIN_XML_SECURE);

                $domainXMLElement = new \SimpleXMLElement($domainXML);
                $uuid = $domainXMLElement->uuid->__toString();
                $macAddresses = DomainXML::findMacAddresses($domainXMLElement);

                DB::transaction(function () use ($request, &$configuration, &$macAddresses, &$computeInstance) {
                    $computeInstance = ComputeInstance::query()->create([
                        "unique_id" => $request->unique_id,
                        // "configuration" => $request->getContent(),
                        "host_only_nic_mac_address" => $macAddresses[NetworkType::TYPE_HOST_ONLY_NETWORK],
                        "status" => StatusCode::STATUS_NORMAL,
                    ]);

                    $networkInterfaceModels = [];
                    foreach ($macAddresses as $type => $macAddress) {
                        $networkInterfaceModels[$type] = ComputeInstanceNetworkInterface::query()->create([
                            "instance_id" => $computeInstance->id,
                            "type" => $type,
                            "mac" => $macAddress,
                        ]);
                    }
                    NetworkInterfaces::storeIPAddressesByType($networkInterfaceModels, $configuration["networkInterfaces"]);
                });
            } catch (\Throwable $throwable) {
                try {
                    (new ComputeInstanceUtils($request->unique_id))->delete(true);
                } catch (\Throwable $e) {
                }
                throw $throwable;
            }

            try {
                $domain->libvirt_domain_create();
            } catch (\Throwable $throwable) {
                try {
                    $computeInstance->networkInterfaces()->delete();
                    $computeInstance->delete();
                } catch (\Throwable $throwable) {
                }

                try {
                    (new ComputeInstanceUtils($request->unique_id))->delete(true);
                } catch (\Throwable $throwable) {
                }
                throw $throwable;
            }
        }

        return ["result" => true, "macAddresses" => $macAddresses, "uuid" => $uuid, "domainXML" => $domainXML];
    }

    public function reconfigure(Request $request, ComputeInstanceResource $computeInstanceResource)
    {
        $configuration = $request->all();
        $configuration["uuid"] = $computeInstanceResource->getComputeInstanceUtils()->getDomain()->libvirt_domain_get_uuid_string();
        $configuration["host_only_nic_mac_address"] = $computeInstanceResource->getComputeInstanceModel()->host_only_nic_mac_address;
        $setupRequestParser = new SetupRequestParser($configuration);
        ComputeInstanceConfigurationLog::query()->create([
            "unique_id" => $request->unique_id,
            "configuration" => $request->getContent(),
        ]);
        $setupRequestParser->reconfigure($generatedXML);

        NetworkInterfaces::storeIPAddressByMacAddressInConfigurations($configuration["networkInterfaces"]);

        return ["result" => true, "domainXML" => $computeInstanceResource->getLibvirtDomain()->libvirt_domain_get_xml_desc(null, VirDomainXMLFlags::VIR_DOMAIN_XML_INACTIVE | VirDomainXMLFlags::VIR_DOMAIN_XML_SECURE)];
    }

    public function changeHostname(Request $request, ComputeInstanceResource $computeInstanceResource)
    {
        $configurator = Loader::newInstance($computeInstanceResource->getComputeInstanceModel()->os, $computeInstanceResource->getUniqueId());
        $configurator->setHostname($request->hostname);
        return ["result" => true];
    }

    public function changeOSPassword(Request $request, ComputeInstanceResource $computeInstanceResource)
    {
        $configurator = Loader::newInstance($computeInstanceResource->getComputeInstanceModel()->os, $computeInstanceResource->getUniqueId());
        $configurator->setPassword($request->password);
        return ["result" => true];
    }

    public function reconfigureOSNetwork(Request $request, ComputeInstanceResource $computeInstanceResource)
    {
        $configurator = Loader::newInstance($computeInstanceResource->getComputeInstanceModel()->os, $computeInstanceResource->getUniqueId());
        $configurator->configureNetwork();
        return ["result" => true];
    }
}
