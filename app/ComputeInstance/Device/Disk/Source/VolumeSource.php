<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-20
 * Time: 下午3:42
 */

namespace App\ComputeInstance\Device\Disk\Source;


class VolumeSource
{
    private $pool;

    private $volume;

    public function __construct($pool, $volume)
    {
        $this->pool = $pool;
        $this->volume = $volume;
    }

    /**
     * @return mixed
     */
    public function getPool()
    {
        return $this->pool;
    }

    /**
     * @return mixed
     */
    public function getVolume()
    {
        return $this->volume;
    }
}