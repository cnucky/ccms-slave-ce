<?php
/**
 * Created by PhpStorm.
 * Date: 19-4-1
 * Time: 下午4:47
 */

namespace App\Utils\System;


class Uptime
{
    public static function getUptime()
    {
        $uptimeContent = file_get_contents("/proc/uptime");
        return floatval($uptimeContent);
    }
}