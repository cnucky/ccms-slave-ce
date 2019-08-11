<?php
/**
 * Created by PhpStorm.
 * Date: 19-3-23
 * Time: 下午4:34
 */

namespace App\Utils\InstanceOSConfigure\Configurator\Debian;


use App\ComputeInstanceNetworkInterface;
use App\Utils\InstanceOSConfigure\Contract\Configurator;
use YunInternet\Libvirt\GuestAgent;

class Debian extends Configurator
{
    const SUPPORTED_OS_LIST = [
        "debian9",
        "ubuntu1804",
    ];

    const FIRST_BOOT_FILE_NAME = "/tmp/first_boot.bash";

    public function firstBoot()
    {
        $this->setPassword($this->getLatestPlaintextPassword());
        $this->configureNetwork();
        $this->setHostname($this->getLatestHostname());
        $this->getGuestAgent()->fileOpen(self::FIRST_BOOT_FILE_NAME, "w", function (GuestAgent $guestAgent, $handle) {
            $guestAgent->fileWrite($handle, "/bin/rm -f /etc/ssh/ssh_host_*\n");
            $guestAgent->fileWrite($handle, "dpkg-reconfigure openssh-server\n");
            $guestAgent->fileWrite($handle, "reboot\n");
        });

        $this->getGuestAgent()->exec("bash", [self::FIRST_BOOT_FILE_NAME]);
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
        $this->getGuestAgent()->fileOpen("/etc/hostname", "w", function (GuestAgent $guestAgent, $handle) use (&$hostname) {
            $guestAgent->fileWrite($handle, $hostname);
        });

        $this->getGuestAgent()->fileOpen("/etc/hosts", "a", function (GuestAgent $guestAgent, $handle) use (&$hostname) {
            $guestAgent->fileWrite($handle, sprintf("127.0.1.1 %s\n", $hostname));
            $guestAgent->fileWrite($handle, sprintf("::1 %s\n", $hostname));
        });
    }

    private function generateIPConfiguration($ips, $networkInterfaceName, $inetSuffix = "")
    {
        $primary = "";
        $aliases = [];

        $aliasSuffix = 0;
        foreach ($ips as $ip) {
            $gateway = $ip->gateway;
            if (!empty($gateway) && empty($primary)) {
                $primary = <<<EOF

auto $networkInterfaceName
iface $networkInterfaceName inet$inetSuffix static
    address $ip->ip/$ip->pool_mask
    gateway $gateway
    dns-nameservers 1.1.1.1 8.8.8.8

EOF;
;
            } else {
                $aliases[] = <<<EOF

auto $networkInterfaceName:$aliasSuffix
iface $networkInterfaceName:$aliasSuffix inet$inetSuffix static
    address $ip->ip/$ip->pool_mask

EOF;
                ++$aliasSuffix;
            }
        }

        // If no primary setting
        if (empty($primary)) {
            array_pop($aliases);
            if ($ip = $ips->last()) {
                $ipAddress = $ip->ip;
                $mask = $ip->pool_mask;
                $primary = <<<EOF

auto $networkInterfaceName
iface $networkInterfaceName inet$inetSuffix static
    address $ipAddress/$mask
    dns-nameservers 1.1.1.1 8.8.8.8

EOF;
            }
        }

        return $primary . implode("", $aliases);
    }
}