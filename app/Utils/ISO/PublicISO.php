<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-17
 * Time: 下午6:33
 */

namespace App\Utils\ISO;


use App\Utils\Libvirt\LibvirtConnection;
use YunInternet\CCMSCommon\Constants\Constants;
use YunInternet\Libvirt\Configuration\StoragePool;
use YunInternet\Libvirt\Exception\ErrorCode;
use YunInternet\Libvirt\Exception\LibvirtException;

class PublicISO
{
    public static function scan()
    {
        $publicISOPool = self::getPublicISOPool();
        return $publicISOPool->libvirt_storagepool_list_volumes();
    }

    public static function getPublicISOPool()
    {
        if (!is_dir(Constants::PUBLIC_ISO_DIRECTORY))
            mkdir(Constants::PUBLIC_ISO_DIRECTORY, 0700, true);

        try {
            $isoStoragePool = LibvirtConnection::getConnection()->storagePoolLookupByName(Constants::PUBLIC_ISO_STORAGE_POOL_NAME);
        } catch (LibvirtException $libvirtException) {
            if ($libvirtException->getCode() !== ErrorCode::STORAGE_POOL_NOT_FOUND)
                throw $libvirtException;
            $storagePool = new StoragePool("dir", Constants::PUBLIC_ISO_STORAGE_POOL_NAME);
            $storagePool->target()->setPath(Constants::PUBLIC_ISO_DIRECTORY);
            $isoStoragePool = LibvirtConnection::getConnection()->storagePoolDefineXML($storagePool->getXML());
        }

        @$isoStoragePool->libvirt_storagepool_set_autostart(true);

        try {
            @$isoStoragePool->libvirt_storagepool_create();
        } catch (LibvirtException $libvirtException) {
            if ($libvirtException->getCode() !== ErrorCode::STORAGE_POOL_IS_ACTIVE)
                throw $libvirtException;
        }

        $isoStoragePool->libvirt_storagepool_refresh();

        return $isoStoragePool;
    }
}