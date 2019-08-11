<?php
/**
 * Created by PhpStorm.
 * Date: 19-3-22
 * Time: 下午9:58
 */

namespace App\Utils\ComputeInstance;


use App\ComputeInstanceNetworkInterface;
use App\ComputeInstanceNetworkInterfaceIPv4;
use App\ComputeInstanceNetworkInterfaceIPv6;
use Illuminate\Support\Facades\DB;

class NetworkInterfaces
{
    /**
     * @param ComputeInstanceNetworkInterface[] $computeInstanceNetworkInterfaces Index by network type
     * @param array $networkConfigurations
     */
    public static function storeIPAddressesByType($computeInstanceNetworkInterfaces, $networkConfigurations)
    {
        DB::transaction(function () use ($computeInstanceNetworkInterfaces, &$networkConfigurations) {
            foreach ($networkConfigurations as $networkConfiguration) {
                $type = $networkConfiguration["type"];
                if (!array_key_exists($type, $computeInstanceNetworkInterfaces))
                    continue;

                /**
                 * @var ComputeInstanceNetworkInterface $networkInterface
                 */
                $networkInterface = $computeInstanceNetworkInterfaces[$type];
                self::storeAddresses($networkInterface, $networkConfiguration);
            }
        });
    }

    public static function storeIPAddressByMacAddressInConfigurations($networkConfigurations)
    {
        DB::transaction(function () use (&$networkConfigurations) {
            foreach ($networkConfigurations as $networkConfiguration) {
                $macAddresses = $networkConfiguration["mac_address"];
                /**
                 * @var ComputeInstanceNetworkInterface $networkInterface
                 */
                $networkInterface = ComputeInstanceNetworkInterface::query()->where("mac", $macAddresses)->firstOrFail();
                self::storeAddresses($networkInterface, $networkConfiguration);
            }
        });
    }

    public static function createValues($networkInterfaceId, $addresses)
    {
        $values = [];
        foreach ($addresses as $address) {
            $values[] = [
                "network_interface_id" => $networkInterfaceId,
                "ip" => $address["ip"],
                "gateway" => $address["pool_gateway"],
                "mask" => $address["mask"],
                "pool_mask" => $address["pool_mask"]
            ];
        }

        return $values;
    }

    private static function storeAddresses(ComputeInstanceNetworkInterface $networkInterface, $networkConfiguration)
    {
        $networkInterface->ipv4s()->delete();
        $networkInterface->ipv6s()->delete();

        $ipv4Values = self::createValues($networkInterface->id, $networkConfiguration["ipv4Addresses"]);
        ComputeInstanceNetworkInterfaceIPv4::query()->insert($ipv4Values);

        $ipv6Values = self::createValues($networkInterface->id, $networkConfiguration["ipv6Addresses"]);
        ComputeInstanceNetworkInterfaceIPv6::query()->insert($ipv6Values);
    }
}