<?php

namespace App\Http\Controllers\StorageVolume;

use App\ComputeInstanceResource;
use App\PublicImage;
use App\StorageVolumeResource;
use App\Utils\ComputeInstanceUtils;
use App\Utils\Libvirt\LibvirtConnection;
use App\Utils\LocalVolume\LocalVolumeFactory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use YunInternet\Libvirt\Constants\Volume\VirStorageVolDeleteFlags;
use YunInternet\Libvirt\Exception\ErrorCode;
use YunInternet\Libvirt\Exception\LibvirtException;

class StorageVolumeController extends Controller
{
    public function resize(Request $request, StorageVolumeResource $storageVolumeResource)
    {
        $this->validate($request, [
            "capacity" => "required|integer|min:0",
        ]);

        $byteSize = $request->capacity * 1092616192;
        if ($byteSize > PHP_INT_MAX)
            return ["result" => false, "message" => "capacity overflow"];

        $flags = 0;
        if ($request->allocate)
            $flags |= VIR_STORAGE_VOL_RESIZE_ALLOCATE;
        if ($request->relativeSize)
            $flags |= VIR_STORAGE_VOL_RESIZE_DELTA;
        if ($request->shrink)
            $flags |= VIR_STORAGE_VOL_RESIZE_SHRINK;

        $storageVolumeResource->getLibvirtStorageVolume()->libvirt_storagevolume_resize($byteSize, $flags);

        return ["result" => true];
    }

    public function newVolume(Request $request)
    {
        $this->validate($request, [
            "uniqueId" => "required",
            "capacity" => "required|integer|min:0",
        ]);

        $localVolumeFactory = new LocalVolumeFactory();
        $localVolume = $localVolumeFactory->withCapacity($request->capacity)->create($request->uniqueId);

        if ($request->attach2Instance) {
            try {
                $computeInstanceUtils = new ComputeInstanceUtils($request->attach2Instance);
                $computeInstanceUtils->attachVolume($localVolume, $request->bus);
                return ["result" => true];
            } catch (\Throwable $throwable) {
                $localVolume->libvirt_storagevolume_delete();
                throw $throwable;
            }
        } else {
            return ["result" => true];
        }
    }

    public function attach(Request $request, ComputeInstanceResource $computeInstanceResource, $volumeUniqueId)
    {
        $computeInstanceUtils = $computeInstanceResource->getComputeInstanceUtils();
        $computeInstanceUtils->attachVolume($volumeUniqueId, $request->bus);
        return ["result" => true];
    }

    public function detach(ComputeInstanceResource $computeInstanceResource, $volumeUniqueId)
    {
        $computeInstanceResource->getComputeInstanceUtils()->getDiskUtils()->detachVolume($volumeUniqueId);
        return ["result" => true];
    }

    public function recreate(Request $request, StorageVolumeResource $storageVolumeResource)
    {
        $storageVolumeResource->getLibvirtStorageVolume()->delete();
        $localVolumeFactory = new LocalVolumeFactory();
        if ($request->publicImage) {
            $publicImage = PublicImage::query()->where("name", $request->publicImage)->firstOrFail();
            $localVolumeFactory->withBackingStore($publicImage->path, $publicImage->format);
        }
        $localVolumeFactory->withCapacity($request->capacity)->create($storageVolumeResource->getUniqueId());

        return ["result" => true];
    }

    public function release(Request $request, StorageVolumeResource $storageVolumeResource)
    {
        $storageVolumeUniqueId = $storageVolumeResource->getUniqueId();
        if ($request->detachFrom) {
            $computeInstanceResource = new ComputeInstanceResource($request->detachFrom);
            try {
                $computeInstanceResource->getComputeInstanceUtils()->getDiskUtils()->detachVolume($storageVolumeUniqueId);
            } catch (LibvirtException $e) {
                if ($e->getCode() !== ErrorCode::STORAGE_VOLUME_NOT_FOUND)
                    throw $e;
            }
        }

        $storageVolumeResource->getLibvirtStorageVolume()->libvirt_storagevolume_delete(VirStorageVolDeleteFlags::VIR_STORAGE_VOL_DELETE_NORMAL);

        return ["result" => true];
    }
}
