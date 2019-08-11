<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-20
 * Time: 下午3:48
 */

namespace App\ComputeInstance\Device\Disk;


use App\ComputeInstance\Device\Disk;
use YunInternet\CCMSCommon\Constants\Domain\Device\Disk\DiskDeviceCode;

class DeviceDisk extends Disk
{
    public function __construct($type, $source)
    {
        parent::__construct($type, DiskDeviceCode::DEVICE_DISK, $source);
    }
}