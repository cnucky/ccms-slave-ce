<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-17
 * Time: 下午4:26
 */

namespace App\Utils\ComputeInstance;


use App\Utils\KeyCounter;
use App\Utils\System\Disk;
use YunInternet\Libvirt\Constants\Constants;

class BusCounter extends KeyCounter
{
    public function formattedName($key)
    {
        $value = $this->value($key);
        $busDeviceNamePrefix = $this->busDeviceNamePrefix($key);
        return Disk::formatDiskName($value, $busDeviceNamePrefix);
    }

    public function formattedNameIncrease($key)
    {
        $value = $this->increase($key);
        $busDeviceNamePrefix = $this->busDeviceNamePrefix($key);
        return Disk::formatDiskName($value, $busDeviceNamePrefix);
    }

    public function value($key)
    {
        $busDeviceNamePrefix = $this->busDeviceNamePrefix($key);
        return parent::value($busDeviceNamePrefix);
    }

    public function increase($key)
    {
        $busDeviceNamePrefix = $this->busDeviceNamePrefix($key);
        return parent::increase($busDeviceNamePrefix);
    }


    protected function busDeviceNamePrefix($key)
    {
        return Constants::BUS_DEVICE_PREFIX[$key];
    }
}