<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-20
 * Time: 下午3:44
 */

namespace App\ComputeInstance\Device\Disk\DeviceDisk;


use App\ComputeInstance\Device\Disk\DeviceDisk;
use YunInternet\CCMSCommon\Constants\Domain\Device\Disk\DiskTypeCode;

class FileSourceDisk extends DeviceDisk
{
    public function __construct($filePath)
    {
        parent::__construct(DiskTypeCode::TYPE_FILE, $filePath);
    }
}