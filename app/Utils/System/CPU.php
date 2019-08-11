<?php
/**
 * Created by PhpStorm.
 * Date: 19-3-27
 * Time: 下午7:45
 */

namespace App\Utils\System;


class CPU
{
    const CPU = 0, USER = 1, NICE = 2, SYSTEM = 3, IDLE = 4, IOWAIT = 5, IRQ = 6, SOFTIRD = 7, STEAL = 8, GUEST = 9, GUEST_NICE = 10;

    public static function getCPUInformation()
    {
        $cpuinfo = file("/proc/cpuinfo");
        $rawCPUModel = substr($cpuinfo[4], 13);
        $cpuModel = preg_replace("/( ){2,}/", " ", $rawCPUModel);

        $processorText = "processor";
        $processorTextLength = strlen($processorText);

        $processors = 0;
        while ($row = array_pop($cpuinfo)) {
            if (strncmp($row, $processorText, $processorTextLength) === 0) {
                $processors = intval(substr($row, 12)) + 1;
                break;
            }
        }

        return [
            "model" => $cpuModel,
            "processors" => $processors,
        ];
    }

    public static function getCPUStatistics()
    {
        $stat = file("/proc/stat");
        return self::statProcess($stat);
    }

    public static function statProcess(array $stat)
    {
        $processors = [];
        foreach ($stat as $row) {
            if (strncmp("cpu", $row, 3))
                break;
            $row = preg_replace("/ {2,}/", " ", trim($row));
            $row_arr = explode(" ", $row);
            $processors[$row_arr[self::CPU]] = $row_arr;
        }
        return $processors;
    }

    public static function calculateUsage(array $first, array $second)
    {
        $results = [];

        foreach (array_keys($first) as $cpu) {
            /*
            $firstIdleTime = self::sumIdle($first[$cpu]);
            $firstTotalNonIdleTime = self::sumNonIdle($first[$cpu]);
            $secondIdleTime = self::sumIdle($second[$cpu]);
            $secondTotalNonIdleTime = self::sumNonIdle($second[$cpu]);
            */

            $secondTotalTime = self::sumTotalTime($second[$cpu]);
            $firstTotalTime = self::sumTotalTime($first[$cpu]);

            $timeDiff = $secondTotalTime - $firstTotalTime;

            // $processorUsage = ($secondTotalNonIdleTime - $firstTotalNonIdleTime) / $timeDiff * 100;

            // $results[$cpu]["usage"] = $processorUsage;
            $results[$cpu]["user"] = ($second[$cpu][self::USER] - $first[$cpu][self::USER]) / $timeDiff * 100;
            $results[$cpu]["nice"] = ($second[$cpu][self::NICE] - $first[$cpu][self::NICE]) / $timeDiff * 100;
            $results[$cpu]["system"] = ($second[$cpu][self::SYSTEM] - $first[$cpu][self::SYSTEM]) / $timeDiff * 100;
            $results[$cpu]["idle"] = ($second[$cpu][self::IDLE] - $first[$cpu][self::IDLE]) / $timeDiff * 100;
            $results[$cpu]["iowait"] = ($second[$cpu][self::IOWAIT] - $first[$cpu][self::IOWAIT]) / $timeDiff * 100;
            $results[$cpu]["irq"] = ($second[$cpu][self::IRQ] - $first[$cpu][self::IRQ]) / $timeDiff * 100;
            $results[$cpu]["softirq"] = ($second[$cpu][self::SOFTIRD] - $first[$cpu][self::SOFTIRD]) / $timeDiff * 100;
            $results[$cpu]["steal"] = ($second[$cpu][self::STEAL] - $first[$cpu][self::STEAL]) / $timeDiff * 100;
            $results[$cpu]["guest"] = ($second[$cpu][self::GUEST] - $first[$cpu][self::GUEST]) / $timeDiff * 100;
            $results[$cpu]["guest_nice"] = ($second[$cpu][self::GUEST_NICE] - $first[$cpu][self::GUEST_NICE]) / $timeDiff * 100;
        }

        return $results;
    }

    public static function sumTotalTime($stat)
    {
        return $stat[self::USER] +
            $stat[self::NICE] +
            $stat[self::SYSTEM] +
            $stat[self::IRQ] +
            $stat[self::SOFTIRD] +
            $stat[self::STEAL] +
            $stat[self::IDLE] +
            $stat[self::IOWAIT]
            ;

    }

    public static function sumNonIdle($stat)
    {
        return $stat[self::USER] +
            $stat[self::NICE] +
            $stat[self::SYSTEM] +
            $stat[self::IRQ] +
            $stat[self::SOFTIRD] +
            $stat[self::STEAL]
            ;
    }

    public static function sumIdle($stat)
    {
        return $stat[self::IDLE] + $stat[self::IOWAIT];
    }
}