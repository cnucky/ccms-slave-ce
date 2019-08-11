<?php
/**
 * Created by PhpStorm.
 * Date: 19-3-19
 * Time: 下午10:26
 */

namespace App\Utils\LocalVolume;


use App\Constants\Storage;
use App\Utils\Libvirt\LibvirtConnection;
use YunInternet\Libvirt\Configuration\StorageVolume;

class LocalVolumeFactory
{
    private $storagePool;

    private $allocation = 0;

    private $capacity;

    private $type = "file";

    private $format = "qcow2";

    private $backingStorePath;

    private $backingStoreFormat;

    public function __construct($storagePoolName = null)
    {
        if (is_null($storagePoolName))
            $storagePoolName = Storage::storagePoolName();
        $this->storagePool = LibvirtConnection::getConnection()->storagePoolLookupByName($storagePoolName);
    }

    public function withAllocation($allocation)
    {
        $this->allocation = $allocation;
        return $this;
    }

    public function withCapacity($capacity)
    {
        $this->capacity = $capacity;
        return $this;
    }

    public function withType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function withFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    public function withBackingStore($path, $format)
    {
        $this->backingStorePath = $path;
        $this->backingStoreFormat = $format;
        return $this;
    }

    public function create($uniqueId)
    {
        $volumeXMLBuilder = new StorageVolume("file", $uniqueId);

        $volumeXMLBuilder
            ->setAllocation($this->allocation)
            ->setCapacity($this->capacity)
        ;

        if (!is_null($this->format)) {
            $volumeXMLBuilder->target()
                ->setFormat($this->format);
        }

        if (!empty($this->backingStorePath) && !empty($this->backingStoreFormat)) {
            $volumeXMLBuilder->useBackingStore($this->backingStorePath, $this->backingStoreFormat);
        }

        return $this->storagePool->storageVolumeCreateXML($volumeXMLBuilder->getXML());
    }
}