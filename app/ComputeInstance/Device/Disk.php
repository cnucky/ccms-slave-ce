<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-20
 * Time: 下午3:38
 */

namespace App\ComputeInstance\Device;


class Disk
{
    private $type;

    private $device;

    private $source;

    /**
     * Disk constructor.
     * @param int $type
     * @param int $device
     * @param mixed $source
     */
    public function __construct($type, $device, $source)
    {
        $this->type = $type;
        $this->device = $device;
        $this->source = $source;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * @return object
     */
    public function getSource()
    {
        return $this->source;
    }
}