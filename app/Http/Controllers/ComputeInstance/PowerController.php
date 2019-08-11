<?php

namespace App\Http\Controllers\ComputeInstance;

use App\ComputeInstance;
use App\ComputeInstanceResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PowerController extends Controller
{
    public function on(Request $request, ComputeInstanceResource $computeInstanceResource)
    {
        if ($request->setFirstBoot) {
            $computeInstanceResource->getComputeInstanceModel()->update(["first_boot" => 1]);
        }
        $computeInstanceResource->getLibvirtDomain()->libvirt_domain_create();
        return ["result" => true];
    }

    public function off(ComputeInstanceResource $computeInstanceResource)
    {
        $computeInstanceResource->getLibvirtDomain()->libvirt_domain_destroy();
        return ["result" => true];
    }

    public function reboot(ComputeInstanceResource $computeInstanceResource)
    {
        $computeInstanceResource->getLibvirtDomain()->libvirt_domain_reboot();
        return ["result" => true];
    }


    public function reset(ComputeInstanceResource $computeInstanceResource)
    {
        $computeInstanceResource->getLibvirtDomain()->libvirt_domain_reset();
        return ["result" => true];
    }
}
