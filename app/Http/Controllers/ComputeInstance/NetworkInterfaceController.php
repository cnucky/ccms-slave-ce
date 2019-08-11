<?php

namespace App\Http\Controllers\ComputeInstance;

use App\ComputeInstanceResource;
use App\Utils\ComputeInstance\NetworkInterfaces;
use App\Utils\ComputeInstance\XMLBuilder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use YunInternet\CCMSCommon\Constants\NetworkInterfaceModelCode;
use YunInternet\CCMSCommon\Constants\NetworkType;
use YunInternet\Libvirt\Configuration\Domain\Device\InterfaceDevice;
use YunInternet\Libvirt\Constants\Domain\VirDomainXMLFlags;

class NetworkInterfaceController extends Controller
{
    public function updateIPAddresses(Request $request, ComputeInstanceResource $computeInstanceResource)
    {
        $interface = $this->findInterface($request->mac_address, $computeInstanceResource);
        unset($interface->filterref);

        $interfaceDevice = new InterfaceDevice($interface["type"]->__toString(), $interface);
        XMLBuilder::applyNWFilter($interfaceDevice, $request->all());

        NetworkInterfaces::storeIPAddressByMacAddressInConfigurations([$request->all()]);

        $computeInstanceResource->getLibvirtDomain()->libvirt_domain_update_device($interface->asXML(), $computeInstanceResource->getLibvirtDomain()->returnLiveTagOnInstanceRunning() | VIR_DOMAIN_DEVICE_MODIFY_CONFIG | VIR_DOMAIN_DEVICE_MODIFY_FORCE);
        return ["result" => true];
    }

    public function changeModel(Request $request, ComputeInstanceResource $computeInstanceResource)
    {
        $model = NetworkInterfaceModelCode::MODEL_CODE_2_TEXT[$request->model];
        if (is_null($model))
            throw ValidationException::withMessages(["Invalid model code"]);
        $interface = $this->findInterface($request->mac_address, $computeInstanceResource);
        $interface->model["type"] = $model;
        $computeInstanceResource->getLibvirtDomain()->libvirt_domain_update_device($interface->asXML(), VIR_DOMAIN_DEVICE_MODIFY_CONFIG);
        return ["result" => true];
    }

    private function findInterface($macAddress, ComputeInstanceResource $computeInstanceResource)
    {
        $simpleXMLElement = new \SimpleXMLElement($computeInstanceResource->getLibvirtDomain()->libvirt_domain_get_xml_desc(null, VirDomainXMLFlags::VIR_DOMAIN_XML_INACTIVE));
        foreach ($simpleXMLElement->devices->interface as $interface) {
            if ($interface->mac["address"]->__toString() === $macAddress)
                return $interface;
        }
        throw ValidationException::withMessages(["Interface with mac address $macAddress not found"]);
    }
}
