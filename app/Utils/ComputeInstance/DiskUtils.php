<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-28
 * Time: 上午1:42
 */

namespace App\Utils\ComputeInstance;


use App\ComputeInstance\Device\Disk\DeviceDisk\FileSourceDisk;
use App\ComputeInstance\Device\Disk\DeviceDisk\VolumeSourceDisk;
use App\ComputeInstance\Device\Disk\Source\VolumeSource;
use App\Constants\Storage;
use App\Utils\ComputeInstance\Exception\ComputeInstanceUtilException;
use App\Utils\ComputeInstanceUtils;
use App\Utils\KeyCounter;
use YunInternet\CCMSCommon\Constants\Domain\Device\Disk\DiskDeviceCode;
use YunInternet\CCMSCommon\Constants\VolumeBusCode;
use YunInternet\Libvirt\Constants\Domain\VirDomainXMLFlags;
use YunInternet\Libvirt\Exception\ErrorCode;
use YunInternet\Libvirt\Exception\LibvirtException;
use YunInternet\Libvirt\StorageVolume;

class DiskUtils
{
    private $computeInstanceUtils;

    public function __construct(ComputeInstanceUtils $computeInstanceUtils)
    {
        $this->computeInstanceUtils = $computeInstanceUtils;
    }

    public function attachVolume($uniqueIdOrStorageVolume, $busCode)
    {
        if ($uniqueIdOrStorageVolume instanceof StorageVolume)
            $uniqueIdOrStorageVolume = $uniqueIdOrStorageVolume->libvirt_storagevolume_get_name();

        $busText = VolumeBusCode::BUS_CODE_2_TEXT[$busCode];

        $busCounter = new BusCounter();
        $keyCounter = new KeyCounter();

        $this->foreachDisk(function (\SimpleXMLElement $simpleXMLElement) use ($busCounter, $keyCounter) {
            $bus = $simpleXMLElement->target["bus"]->__toString();
            $busCounter->increase($bus);
            $keyCounter->increase($bus);
        }, true);

        $flag = $this->computeInstanceUtils->getDomain()->returnLiveTagOnInstanceRunning() | VIR_DOMAIN_DEVICE_MODIFY_CONFIG;

        // Attach virtio-scsi controller if need
        if ($busCode == VolumeBusCode::BUS_SCSI) {
            // Do not forget the device to be added later, so + 1
            $scsiDeviceCount = $keyCounter->value(VolumeBusCode::BUS_CODE_2_TEXT[VolumeBusCode::BUS_SCSI]) + 1;
            $currentSCSIControllerCount = $this->computeInstanceUtils->scsiControllerCount();
            $scsiControllerRequirements = DiskUtils::SCSIControllerRequirements($scsiDeviceCount);
            $need2Attach = $scsiControllerRequirements - $currentSCSIControllerCount;

            // If use $flags = VIR_DOMAIN_AFFECT_CONFIG | VIR_DOMAIN_AFFECT_LIVE: internal error: Cannot parse controller index -1
            if ($this->computeInstanceUtils->getDomain()->libvirt_domain_is_active()) {
                for ($i = 0; $i < $need2Attach; ++$i) {
                    $this->computeInstanceUtils->getDomain()->libvirt_domain_attach_device("<controller type='scsi' model='virtio-scsi'/>", VIR_DOMAIN_AFFECT_LIVE);
                }
            }
            for ($i = 0; $i < $need2Attach; ++$i) {
                $this->computeInstanceUtils->getDomain()->libvirt_domain_attach_device("<controller type='scsi' model='virtio-scsi'/>", VIR_DOMAIN_AFFECT_CONFIG);
            }
        }

        $diskConfigurationBuilder = new \YunInternet\Libvirt\Configuration\Domain\Device\Disk("volume", "disk", new \SimpleXMLElement("<disk/>"));
        $diskConfigurationBuilder
            ->setDriver("qemu")
            ->setDriverType("qcow2")
            ->volumeSource(Storage::storagePoolName(), $uniqueIdOrStorageVolume)
            ->setTargetDevice($busCounter->formattedName($busText))
            ->setTargetBus($busText)
        ;
        if ($busCode == VolumeBusCode::BUS_SCSI) {
            $diskConfigurationBuilder->vendor()->setValue(Storage::DEFAULT_SCSI_VENDOR);
            $diskConfigurationBuilder->product()->setValue(Storage::DEFAULT_SCSI_PRODUCT);
        }
        $diskConfigurationBuilder->serial()->setValue($uniqueIdOrStorageVolume);

        $max = 256;
        for ($i = 1; $i <= $max; ++$i) {
            try {
                $this->computeInstanceUtils->getDomain()->libvirt_domain_attach_device($diskConfigurationBuilder->getXML(), $flag);
                break;
            } catch (LibvirtException $e) {
                if ($e->getCode() !== ErrorCode::TARGET_ALREADY_EXISTS || $i === $max) {
                    throw $e;
                }
                // Try next one
                $busCounter->increase($busText);
                $diskConfigurationBuilder
                    ->setTargetDevice($busCounter->formattedName($busText))
                ;
            }
        }
    }


    public function detachVolume($volumeUniqueId)
    {
        $result = false;
        $this->foreachDisk(function (\SimpleXMLElement $simpleXMLElement) use ($volumeUniqueId, &$result) {
            if ($simpleXMLElement->serial->__toString() === $volumeUniqueId) {
                $this->computeInstanceUtils->getDomain()->libvirt_domain_detach_device($simpleXMLElement->asXML(), $this->computeInstanceUtils->returnLiveTagOnInstanceRunning() | VIR_DOMAIN_AFFECT_CONFIG);
                $result = true;
                return;
            }
        });

        if (!$result)
            throw new LibvirtException("volume ". $volumeUniqueId ." not found", ErrorCode::STORAGE_VOLUME_NOT_FOUND);
    }

    /**
     * Change CDROM media
     * @param int $index CDROM index, begin at 0
     * @param mixed $source string for file source, or App\ComputeInstance\Device\Disk\Source\VolumeSoruce
     * @return void
     * @throws ComputeInstanceUtilException
     */
    public function changeCDROMMedia($index, $source = null)
    {
        $this->changeMedia(DiskDeviceCode::DEVICE_CDROM, $index, $source);
    }

    /**
     * @param int $mediaDeviceCode Code set in YunInternet\CCMSCommon\Constants\Domain\Device\Disk\DiskDeviceCode
     * @param int $index Device index, begin at 0
     * @param mixed $source string for file source, or App\ComputeInstance\Device\Disk\Source\VolumeSoruce, set null to eject media
     * @return void
     * @throws ComputeInstanceUtilException
     */
    public function changeMedia($mediaDeviceCode, $index, $source = null)
    {
        $devices = $this->listDiskByType(DiskDeviceCode::DEVICE_CODE_2_TEXT[$mediaDeviceCode]);
        if (!array_key_exists($index, $devices))
            throw new ComputeInstanceUtilException("Index $index for media type " . $mediaDeviceCode . " not exists", ComputeInstanceUtilException::DISK_NOT_FOUND);

        /**
         * @var \SimpleXMLElement $mediaDevice
         */
        $mediaDevice = $devices[$index];

        unset($mediaDevice->source);

        $mediaDevice->target["tray"] = "closed";

        if (is_null($source)) {
            $mediaDevice["type"] = "file";
        } else if (is_string($source)) {
            $mediaDevice["type"] = "file";
            $mediaDevice->source["file"] = $source;
        } else if ($source instanceof VolumeSource) {
            $mediaDevice["type"] = "volume";
            $mediaDevice->source["pool"] = $source->getPool();
            $mediaDevice->source["volume"] = $source->getVolume();
        }

        if (is_null($mediaDevice->readonly))
            $mediaDevice->addChild("<readonly/>");

        $this->computeInstanceUtils->getDomain()->libvirt_domain_update_device($mediaDevice->asXML(), $this->computeInstanceUtils->returnLiveTagOnInstanceRunning() | VIR_DOMAIN_DEVICE_MODIFY_CONFIG | VIR_DOMAIN_DEVICE_MODIFY_FORCE);
    }

    /**
     * List disk which device value equal to disk
     * @return \App\ComputeInstance\Device\Disk[]
     */
    public function listVolumes()
    {
        $volumes = [];

        $domainXMLElement = new \SimpleXMLElement($this->computeInstanceUtils->getDomain()->libvirt_domain_get_xml_desc(null));
        foreach ($domainXMLElement->devices->disk as $disk) {
            $type = @$disk["type"]->__toString();
            $device = @$disk["device"]->__toString();

            if ($device === "disk") {
                switch ($type) {
                    case "volume":
                        $pool = @$disk->source["pool"]->__toString();
                        $volume = @$disk->source["volume"]->__toString();

                        $volumes[] = new VolumeSourceDisk($pool, $volume);
                        break;
                    case "file":
                        $file = @$disk->source["file"]->__toString();
                        $volumes[] = new FileSourceDisk($file);
                        break;
                }
            }
        }

        return $volumes;
    }

    public function listCDROMs()
    {
        return $this->listDiskByType("cdrom");
    }

    public function listDiskByType($type)
    {
        $disks = [];

        $this->foreachDisk(function (\SimpleXMLElement $disk) use ($type, &$disks) {
            $device = @$disk["device"]->__toString();
            if ($device === $type)
                $disks[] = $disk;
        });

        return $disks;
    }

    /**
     * Disk iterator
     * @param callable $filter
     * @param bool $inactiveFlag
     */
    public function foreachDisk(callable $filter, $inactiveFlag = false)
    {
        foreach ($this->computeInstanceUtils->domainSimpleXMLElement(null, $inactiveFlag ? VirDomainXMLFlags::VIR_DOMAIN_XML_INACTIVE : 0)->devices->disk as $disk) {
            $filter($disk);
        }
    }

    public static function SCSIControllerRequirements($scsiDiskCount)
    {
        if ($scsiDiskCount == 0)
            return 0;
        $count = intdiv($scsiDiskCount, 7) + 1;
        return $count;
    }
}