<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-11
 * Time: 下午8:47
 */

namespace App\Utils\System;


class Memory
{
    public static function memoryInformation()
    {
        $formattedMemoryInformation = self::meminfoFormat();

        return [
            "total" => intval($formattedMemoryInformation["MemTotal"]),
            "free" => intval($formattedMemoryInformation["MemFree"]),
            "available" => intval($formattedMemoryInformation["MemAvailable"]),
        ];
    }

    public static function meminfoFormat()
    {
        $formattedMemoryInformation = [];

        $explodedMeminfo = file("/proc/meminfo");
        foreach ($explodedMeminfo as $row) {
            @list($name, $valueWithUnit) = explode(":", $row);
            $name = trim($name);
            if (empty($name))
                continue;
            list($value) = explode(" ", trim($valueWithUnit));
            $formattedMemoryInformation[$name] = trim($value);
        }

        return $formattedMemoryInformation;
    }
}