<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-27
 * Time: 下午9:22
 */

namespace App\Utils\ComputeInstance\Exception;


class ComputeInstanceUtilException extends \Exception
{
    const DISK_NOT_FOUND = 10001;

    const VNC_GRAPHIC_NOT_FOUND = 10002;
}