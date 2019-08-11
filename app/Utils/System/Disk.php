<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-11
 * Time: 下午11:31
 */

namespace App\Utils\System;


class Disk
{
    const BLOCK_DEV = 2, READS_COMPLETED = 3, READS_MERGED = 4, SECTORS_READ = 5, MILLISECONDS_SPENT_READING = 6, WRITES_COMPLETED = 7, WRITES_MERGED = 8, SECTORS_WRITTEN = 9, MILLISECONDS_SPENT_WRITING = 10, I_AND_OS_CURRENTLY_IN_PROGRESS = 11;

    const SECTOR_SIZE = 512;

    public static function getBlockDevices()
    {
        $devices = [];
        $fp = fopen("/proc/1/mounts", "r");
        while ($line = fgets($fp)) {
            list($device) = explode(" ", $line);
            if (strncmp($device, "/dev/", 5) === 0)
                $devices[$device] = $device;
        }

        return $devices;
    }

    public static function formatDiskName($index, $prefix = "")
    {
        $ascii_a = ord('a');
        $base = ord('z') - $ascii_a + 1;
        $unit = $base;
        $deviceCharIndex = "";
        do {
            $char = chr($ascii_a + ($index % $unit));
            $index = intdiv($index, $unit) - 1;
            $deviceCharIndex = $char . $deviceCharIndex;
        } while ($index >= 0);

        return $prefix . $deviceCharIndex;
    }

    public static function getDiskStats()
    {
        $diskStat = file("/proc/diskstats");

        $records = [];
        foreach ($diskStat as $row) {
            $row = trim($row);
            if (empty($row))
                continue;
            $row = preg_replace("/ {2,}/", " ", $row);
            $rowExploded = explode(" ", $row);
            $records[$rowExploded[self::BLOCK_DEV]] = $rowExploded;
        }

        return $records;
    }

    public static function calculateUsage($first, $second, $timeDiffInSecond)
    {
        $results = [];

        foreach (array_keys($first) as $blockDev) {
            if (!array_key_exists($blockDev, $second))
                continue;
            $results[$blockDev]["read_bytes_per_second"] = ($second[$blockDev][self::SECTORS_READ] - $first[$blockDev][self::SECTORS_READ]) / $timeDiffInSecond * self::SECTOR_SIZE;
            $results[$blockDev]["write_bytes_per_second"] = ($second[$blockDev][self::SECTORS_WRITTEN] - $first[$blockDev][self::SECTORS_WRITTEN]) / $timeDiffInSecond * self::SECTOR_SIZE;
        }

        return $results;
    }
}