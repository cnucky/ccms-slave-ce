<?php
/**
 * Created by PhpStorm.
 * Date: 19-3-6
 * Time: 上午2:29
 */

namespace App\Utils\Floppy;


use App\Utils\Libvirt\LibvirtConnection;
use App\Utils\Libvirt\Storage\Pool;
use YunInternet\CCMSCommon\Constants\Constants;
use YunInternet\Libvirt\Exception\ErrorCode;
use YunInternet\Libvirt\Exception\LibvirtException;

class PublicFloppy
{
    public static function scan()
    {
        $storagePool = self::getPublicFloppyPool();
        return $storagePool->libvirt_storagepool_list_volumes();
    }

    public static function getPublicFloppyPool()
    {
        if (!is_dir(Constants::PUBLIC_FLOPPY_DIRECTORY))
            mkdir(Constants::PUBLIC_FLOPPY_DIRECTORY, 0700, true);

        $storagePool = Pool::findOrCreate(Constants::PUBLIC_FLOPPY_STORAGE_POOL_NAME, Constants::PUBLIC_FLOPPY_DIRECTORY);
        $storagePool->libvirt_storagepool_refresh();

        return $storagePool;
    }
}