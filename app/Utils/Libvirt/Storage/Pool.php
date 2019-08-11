<?php
/**
 * Created by PhpStorm.
 * Date: 19-3-6
 * Time: 上午2:34
 */

namespace App\Utils\Libvirt\Storage;


use App\Utils\Libvirt\LibvirtConnection;
use YunInternet\Libvirt\Exception\ErrorCode;
use YunInternet\Libvirt\Exception\LibvirtException;
use YunInternet\Libvirt\StoragePool;

class Pool
{
    /**
     * @param $name
     * @param $target
     * @return StoragePool
     * @throws LibvirtException
     */
    public static function findOrCreate($name, $target)
    {
        try {
            $storagePoolInstance = LibvirtConnection::getConnection()->storagePoolLookupByName($name);
        } catch (LibvirtException $libvirtException) {
            if ($libvirtException->getCode() !== ErrorCode::STORAGE_POOL_NOT_FOUND)
                throw $libvirtException;
            $storagePool = new \YunInternet\Libvirt\Configuration\StoragePool("dir", $name);
            $storagePool->target()->setPath($target);
            $storagePoolInstance = LibvirtConnection::getConnection()->storagePoolDefineXML($storagePool->getXML());
        }

        @$storagePoolInstance->libvirt_storagepool_set_autostart(true);

        try {
            @$storagePoolInstance->libvirt_storagepool_create();
        } catch (LibvirtException $libvirtException) {
            if ($libvirtException->getCode() !== ErrorCode::STORAGE_POOL_IS_ACTIVE)
                throw $libvirtException;
        }

        return $storagePoolInstance;
    }
}