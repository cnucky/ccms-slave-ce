<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-20
 * Time: 下午3:33
 */

namespace App\Utils;


use App\ComputeInstance\Device\Disk;
use App\Constants\Storage;
use App\Utils\ComputeInstance\BusCounter;
use App\Utils\ComputeInstance\DiskUtils;
use App\Utils\ComputeInstance\Exception\ComputeInstanceUtilException;
use App\Utils\Libvirt\LibvirtConnection;
use YunInternet\CCMSCommon\Constants\Domain\Device\Disk\DiskTypeCode;
use YunInternet\CCMSCommon\Constants\VolumeBusCode;
use YunInternet\Libvirt\Constants\Constants;
use YunInternet\Libvirt\Constants\Domain\VirDomainXMLFlags;
use YunInternet\Libvirt\Exception\ErrorCode;
use YunInternet\Libvirt\Exception\LibvirtException;
use YunInternet\Libvirt\StorageVolume;

class ComputeInstanceUtils
{
    private $uniqueId;

    /**
     * @var \YunInternet\Libvirt\Domain
     */
    private $domain;

    /**
     * @var DiskUtils
     */
    private $diskUtils;

    public function __construct($computeInstanceUniqueId)
    {
        $this->uniqueId = $computeInstanceUniqueId;
        $this->domain = LibvirtConnection::getConnection()->domainLookupByName($computeInstanceUniqueId);
    }

    public function attachVolume($uniqueIdOrStorageVolume, $busCode)
    {
        return $this->getDiskUtils()->attachVolume(... func_get_args());
    }

    public function foreachController(callable $filter, $inactiveFlag = false)
    {
        foreach ($this->domainSimpleXMLElement(null, $inactiveFlag ? VirDomainXMLFlags::VIR_DOMAIN_XML_INACTIVE : 0)->devices->controller as $controller) {
            $filter($controller);
        }
    }

    public function scsiControllerCount()
    {
        $count = 0;
        $this->foreachController(function (\SimpleXMLElement $controller) use (&$count) {
            if ($controller["type"]->__toString() === "scsi") {
                ++$count;
            }
        }, true);
        return $count;
    }

    public function delete($withRelatedVolumes = false)
    {
        try {
            $domain = LibvirtConnection::getConnection()->domainLookupByName($this->uniqueId);
            try {
                @$domain->libvirt_domain_destroy();
            } catch (LibvirtException $e) {
            }

            // $this->deleteVolume(Storage::DEFAULT_CONFIGURATION_STORAGE_POOL_NAME, $this->uniqueId);

            if ($withRelatedVolumes) {
                foreach ($this->getDiskUtils()->listVolumes() as $volume) {
                    $source = $volume->getSource();

                    switch ($volume->getType()) {
                        case DiskTypeCode::TYPE_VOLUME:
                            /**
                             * @var Disk\Source\VolumeSource $source
                             */
                            $this->deleteVolume($source->getPool(), $source->getVolume());
                            break;
                        case DiskTypeCode::TYPE_FILE:
                            $this->deleteFile($source);
                            break;
                    }
                }
            }

            $domain->libvirt_domain_undefine_flags(VIR_DOMAIN_UNDEFINE_SNAPSHOTS_METADATA | VIR_DOMAIN_UNDEFINE_NVRAM);
        } catch (LibvirtException $libvirtException) {
            if ($libvirtException->getCode() !== ErrorCode::DOMAIN_NOT_FOUND)
                throw $libvirtException;
        }

        /*
        try {
            \App\ComputeInstance::query()->where("unique_id", $this->configuration["unique_id"])->firstOrFail()->delete();
        } catch (ModelNotFoundException $e) {
        }
        */
    }

    /**
     * @param $password
     * @return bool
     * @throws LibvirtException
     */
    public function setVNCPassword($password)
    {
        return $this->domain->setVNCPassword($password);
    }

    /**
     * @return \SimpleXMLElement
     */
    public function domainSimpleXMLElement($xpath = null, $flags = VirDomainXMLFlags::VIR_DOMAIN_XML_SECURE)
    {
        return $this->domain->domainSimpleXMLElement($xpath, $flags);
    }

    /**
     * @return mixed
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * @return \YunInternet\Libvirt\Domain
     */
    public function getDomain(): \YunInternet\Libvirt\Domain
    {
        return $this->domain;
    }


    /**
     * @return DiskUtils
     */
    public function getDiskUtils() : DiskUtils
    {
        if (is_null($this->diskUtils))
            $this->diskUtils = new DiskUtils($this);
        return $this->diskUtils;
    }

    public function status()
    {
        return [
            "power" => $this->domain->libvirt_domain_is_active(),
        ];
    }

    public function returnLiveTagOnInstanceRunning()
    {
        return $this->domain->returnLiveTagOnInstanceRunning();
    }

    /**
     * @return self[]
     */
    public static function all()
    {
        $utils = [];

        foreach (LibvirtConnection::getConnection()->libvirt_list_domains() as $domain) {
            $utils[] = new self($domain);
        }

        return $utils;
    }

    public static function allStatus()
    {
        $status = [];

        foreach (self::all() as $computeInstanceUtils)
            $status[$computeInstanceUtils->getUniqueId()] = $computeInstanceUtils->status();

        return $status;
    }



    private function deleteVolume($pool, $volume)
    {
        $storagePool = LibvirtConnection::getConnection()->storagePoolLookupByName($pool);

        try {
            $storageVolume = $storagePool->storageVolumeLookupByName($volume);
            $storageVolume->libvirt_storagevolume_delete();
        } catch (LibvirtException $libvirtException) {
            if ($libvirtException->getCode() !== ErrorCode::STORAGE_POOL_NOT_FOUND && $libvirtException->getCode() !== ErrorCode::STORAGE_VOLUME_NOT_FOUND)
                throw $libvirtException;
        }
    }

    private function deleteFile($file)
    {
        @unlink($file);
    }
}