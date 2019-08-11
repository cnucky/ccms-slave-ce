<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-20
 * Time: 下午3:44
 */

namespace App\ComputeInstance\Device\Disk\DeviceDisk;


use App\ComputeInstance\Device\Disk\DeviceDisk;
use App\ComputeInstance\Device\Disk\Source\VolumeSource;
use YunInternet\CCMSCommon\Constants\Domain\Device\Disk\DiskTypeCode;

class VolumeSourceDisk extends DeviceDisk
{
    public function __construct($pool, $volume)
    {
        parent::__construct(DiskTypeCode::TYPE_VOLUME, new VolumeSource($pool, $volume));
    }
}