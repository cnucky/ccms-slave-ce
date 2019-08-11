<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-11
 * Time: 下午11:09
 */

namespace App\Utils\System;


class Network
{
    const DEFAULT_INTERVAL = 1;

    const NIC = 0, RX_BYTES = 1, RX_PACKETS = 2, RX_ERRS = 3, RX_DROP = 4, RX_FIFO = 5, RX_FRAME = 6, RX_COMPRESSED = 7, RX_MULTICAST = 8, TX_BYTES = 9, TX_PACKETS = 10, TX_ERRS = 11, TX_DROP = 12, TX_FIFO = 13, TX_FRAME = 14, TX_COMPRESSED = 15, TX_MULTICAST = 16;

    public static function currentBandwidthUsage($interval = self::DEFAULT_INTERVAL)
    {
        $first = self::formatNetDeviceStatus();
        sleep($interval);
        $second = self::formatNetDeviceStatus();

        return self::calculateUsage($first, $second, $interval);
    }

    public static function calculateUsage(array $first, array $second, $timeDiffInSecond)
    {
        $results = [];

        foreach (array_keys($first) as $nic) {
            if (is_array(@$second[$nic])) {
                $results[$nic]["rx_bytes_per_second"] = (double) ($second[$nic][self::RX_BYTES] - $first[$nic][self::RX_BYTES]) / $timeDiffInSecond;
                $results[$nic]["rx_packets_per_second"] = (double) ($second[$nic][self::RX_PACKETS] - $first[$nic][self::RX_PACKETS]) / $timeDiffInSecond;
                $results[$nic]["tx_bytes_per_second"] = (double) ($second[$nic][self::TX_BYTES] - $first[$nic][self::TX_BYTES]) / $timeDiffInSecond;
                $results[$nic]["tx_packets_per_second"] = (double) ($second[$nic][self::TX_PACKETS] - $first[$nic][self::TX_PACKETS]) / $timeDiffInSecond;
            }
        }

        return $results;
    }

    public static function calculateTrafficUsage($first, $second)
    {
        $results = [];

        foreach (array_keys($first) as $nic) {
            if (!array_key_exists($nic, $second))
                continue;
            $results[$nic]["rx_bytes"] = $second[$nic][self::RX_BYTES] - $first[$nic][self::RX_BYTES];
            $results[$nic]["rx_packets"] = $second[$nic][self::RX_PACKETS] - $first[$nic][self::RX_PACKETS];
            $results[$nic]["tx_bytes"] = $second[$nic][self::TX_BYTES] - $first[$nic][self::TX_BYTES];
            $results[$nic]["tx_packets"] = $second[$nic][self::TX_PACKETS] - $first[$nic][self::TX_PACKETS];
        }

        return $results;
    }

    public static function formatNetDeviceStatus()
    {
        $netDevStatus = file_get_contents("/proc/net/dev");

        $netDevStatusList = explode("\n", $netDevStatus);

        unset($netDevStatusList[0], $netDevStatusList[1]);

        $status = [];

        foreach ($netDevStatusList as $row) {
            if (empty($row))
                continue;
            $row = preg_replace("/ {2,}/", " ", trim($row));
            $explodedRow = explode(" ", $row);
            $explodedRow[self::NIC] = trim($explodedRow[self::NIC], ":");
            $status[$explodedRow[self::NIC]] = $explodedRow;
        }

        return $status;
    }
}