<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-16
 * Time: 下午5:02
 */

namespace App\Constants;


abstract class Storage
{
    const DEFAULT_STORAGE_POOL_TYPE = "dir";

    const DEFAULT_STORAGE_POOL_NAME = "ccms-default";

    const DEFAULT_STORAGE_POOL_TARGET = "/ccms-virt/pools/" . self::DEFAULT_STORAGE_POOL_NAME;

    const DEFAULT_SCSI_VENDOR = "CCMS";

    const DEFAULT_SCSI_PRODUCT = "volume";

    const DEFAULT_CONFIGURATION_LOOPBACK_FILE_PATH = "/ccms-virt/configuration-loopback";

    const DEFAULT_CONFIGURATION_STORAGE_POOL_NAME = "ccms-configurations";

    public static function storagePoolType()
    {
        return env('STORAGE_POOL_TYPE', self::DEFAULT_STORAGE_POOL_TYPE);
    }

    public static function storagePoolName()
    {
        return env('STORAGE_POOL_NAME', self::DEFAULT_STORAGE_POOL_NAME);
    }

    public static function storagePoolTarget()
    {
        return env('STORAGE_POOL_TARGET', self::DEFAULT_STORAGE_POOL_TARGET);
    }

    public static function configurationLoopbackDevice()
    {
        return env("CONFIGURATION_LOOPBACK_DEVICE", "/dev/loop6");
    }
}