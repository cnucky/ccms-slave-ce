<?php
/**
 * Created by PhpStorm.
 * Date: 19-5-7
 * Time: 上午12:36
 */

namespace App\Utils\InstanceOSConfigure\Configurator\CentOS;


use App\ComputeInstanceNetworkInterface;
use App\Utils\InstanceOSConfigure\Contract\Configurator;
use App\Utils\InstanceOSConfigure\Exception\ConfiguratorException;

class CentOS extends Configurator
{
    const SUPPORTED_OS_LIST = [
        // "CentOS7" => true,
    ];

    public function firstBoot()
    {
        // TODO: Implement firstBoot() method.
    }

    public function configureNetwork()
    {
        $this->getGuestAgent()->exec("rm", ["-f", "/etc/resolv.conf"]);
        $networkConfigurations = "";

        $networkInterfaceModels = $this->getNetworkInterfaceModelKeyByMac();
        foreach ($this->getGuestNetworkInterfaces() as $networkInterface) {
            if (array_key_exists("hardware-address", $networkInterface)) {
                $macAddress = $networkInterface["hardware-address"];
                if ($networkInterfaceModels->has($macAddress)) {
                    $name = $networkInterface["name"];
                    /**
                     * @var ComputeInstanceNetworkInterface $networkInterfaceModel
                     */
                    $networkInterfaceModel = $networkInterfaceModels->get($macAddress);

                    $ipv4Configuration = "";

                    foreach ($networkInterfaceModel->ipv4s as $index => $ipv4) {
                        $ipv4Configuration .= sprintf("IPADDR%s=%s\nPREFIX%s=%s\n", $index, $ipv4->ip, $index, $ipv4->pool_mask);
                    }

                    $networkConfigurations = <<<EOF
DEVICE="eth0"
NM_CONTROLLED="no"
ONBOOT="yes"
BOOTPROTO=static
IPV6INIT=yes
EOF;


                    $networkConfigurations .= $this->generateIPConfiguration($networkInterfaceModel->ipv4s, $name);
                    $networkConfigurations .= $this->generateIPConfiguration($networkInterfaceModel->ipv6s, $name, "6");
                }
            }
        }

        $this->getGuestAgent()->fileOpen("/etc/network/interfaces", "w", function (GuestAgent $guestAgent, $handle) use (&$networkConfigurations) {
            $guestAgent->fileWrite($handle, <<<EOF
# This file describes the network interfaces available on your system
# and how to activate them. For more information, see interfaces(5).

source /etc/network/interfaces.d/*

# The loopback network interface
auto lo
iface lo inet loopback

EOF
            );
            $guestAgent->fileWrite($handle, $networkConfigurations);
        });

        try {
            $this->getGuestAgent()->fileOpen("/etc/resolv.conf", "w", function (GuestAgent $guestAgent, $handle) {
                $guestAgent->fileWrite($handle, <<<EOF
nameserver 1.1.1.1
nameserver 8.8.8.8

EOF
                );
            });
        } catch (\Exception $e) {
        }
    }

    public function setPassword($plaintextPassword)
    {
        $this->getGuestAgent()->setPlainTextPassword("root", $plaintextPassword);
    }

    public function setHostname($hostname)
    {
        // TODO: Implement setHostname() method.
    }
}