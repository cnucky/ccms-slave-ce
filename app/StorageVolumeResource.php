<?php
/**
 * Created by PhpStorm.
 * Date: 19-3-18
 * Time: 下午6:50
 */

namespace App;


use App\Constants\Storage;
use App\Utils\Libvirt\LibvirtConnection;

class StorageVolumeResource
{
    private $uniqueId;

    private $libvirtStorageVolume;

    public function __construct($uniqueId)
    {
        $this->uniqueId = $uniqueId;
        $this->libvirtStorageVolume = LibvirtConnection::getConnection()->storagePoolLookupByName(Storage::storagePoolName())->storageVolumeLookupByName($uniqueId);
    }

    /**
     * @return mixed
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * @return \YunInternet\Libvirt\StorageVolume
     */
    public function getLibvirtStorageVolume(): \YunInternet\Libvirt\StorageVolume
    {
        return $this->libvirtStorageVolume;
    }
}