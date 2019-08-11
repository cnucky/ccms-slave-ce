<?php

namespace App\Http\Controllers\ComputeInstance;

use App\ComputeInstance\Device\Disk\Source\VolumeSource;
use App\ComputeInstanceResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use YunInternet\CCMSCommon\Constants\Constants;
use YunInternet\CCMSCommon\Constants\Domain\Device\Disk\DiskDeviceCode;
use YunInternet\CCMSCommon\Constants\Domain\Device\Disk\DiskDeviceCode2StoragePoolName;

class MediaController extends Controller
{
    public function changeMedia(ComputeInstanceResource $computeInstanceResource, $diskDeviceCode, $deviceIndex, $mediaInternalName = null)
    {
        switch ($diskDeviceCode) {
            case DiskDeviceCode::DEVICE_CDROM:
            case DiskDeviceCode::DEVICE_FLOPPY:
                $storagePoolName = $this->diskDeviceCode2StoragePoolName($diskDeviceCode);
                if (!empty($storagePoolName)) {
                    $source = null;
                    if (!is_null($mediaInternalName)) {
                        $source = new VolumeSource($storagePoolName, $mediaInternalName);
                    }
                    $computeInstanceResource->getComputeInstanceUtils()->getDiskUtils()->changeMedia($diskDeviceCode, $deviceIndex, $source);
                    return ["result" => true];
                }
        }

        RETURN_ERROR:
        return ["result" => false, "message" => "Invalid disk device code"];
    }

    private function diskDeviceCode2StoragePoolName($diskDeviceCode)
    {
        return DiskDeviceCode2StoragePoolName::diskDeviceCode2StoragePoolName($diskDeviceCode);
    }
}
