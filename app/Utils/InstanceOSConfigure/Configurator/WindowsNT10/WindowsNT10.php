<?php
/**
 * Created by PhpStorm.
 * Date: 19-3-23
 * Time: 下午2:55
 */

namespace App\Utils\InstanceOSConfigure\Configurator\WindowsNT10;


use App\ComputeInstanceNetworkInterface;
use App\Utils\InstanceOSConfigure\Contract\Configurator;
use YunInternet\CCMSCommon\Constants\NetworkType;
use YunInternet\Libvirt\GuestAgent;

class WindowsNT10 extends Configurator
{
    const SUPPORTED_OS_LIST = [
        "win2008R2",
        "win2012",
        "win10",
        "win2016",
        "win2019",
    ];

    const FIRST_BOOT_FILE_PATH = "C:/first_boot.bat";
    const CONFIGURE_NETWORK_FILE_PATH = "C:/network_configure.bat";
    const SET_HOSTNAME_FILE_PATH = "C:/set_hostname.bat";

    public function firstBoot()
    {
        $latestConfiguration = $this->getLatestConfiguration();

        $this->setPassword($latestConfiguration["password"]);
        $networkConfigureCommand = $this->generateNetworkConfigureCommand();
        $setHostnameCommand = $this->generateSetHostnameCommand($latestConfiguration["hostname"]);
        $rebootCommand = $this->generateRebootCommand();

        $this->getGuestAgent()->fileOpen(self::FIRST_BOOT_FILE_PATH, "w", function (GuestAgent $guestAgent, $handle) use (&$networkConfigureCommand, $setHostnameCommand, $rebootCommand) {
                $guestAgent->fileWrite($handle, $networkConfigureCommand);
                $guestAgent->fileWrite($handle, $setHostnameCommand);
                $guestAgent->fileWrite($handle, $rebootCommand);
        });

        $this->getGuestAgent()->exec(self::FIRST_BOOT_FILE_PATH);
        return true;
    }

    public function configureNetwork()
    {
        $networkConfigureCommand = $this->generateNetworkConfigureCommand();
        $this->getGuestAgent()->fileOpen(self::CONFIGURE_NETWORK_FILE_PATH, "w", function (GuestAgent $guestAgent, $handle) use (&$networkConfigureCommand) {
            $guestAgent->fileWrite($handle, $networkConfigureCommand);
        });
        $this->getGuestAgent()->exec(self::CONFIGURE_NETWORK_FILE_PATH);
        return true;
    }

    public function setPassword($plaintextPassword)
    {
        $this->getGuestAgent()->setPlainTextPassword("Administrator", $plaintextPassword);
        return true;
    }

    public function setHostname($hostname)
    {
        $setHostnameCommand = $this->generateSetHostnameCommand($hostname);
        $this->getGuestAgent()->fileOpen(self::SET_HOSTNAME_FILE_PATH, "w", function (GuestAgent $guestAgent, $handle) use ($setHostnameCommand) {
            $guestAgent->fileWrite($handle, $setHostnameCommand);
        });
        $this->getGuestAgent()->exec(self::SET_HOSTNAME_FILE_PATH);
        return true;
    }

    private function generateNetworkConfigureCommand()
    {
        $fullCommand = "";

        $fullCommand .= sprintf("route delete 0.0.0.0 mask 0.0.0.0\r\n");
        $fullCommand .= sprintf("route delete ::/0\r\n");

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

                    $fullCommand .= sprintf("netsh interface ipv4 delete dnsservers \"%s\" all\r\n", $name);
                    $fullCommand .= $this->generateNetshIPv4Command($networkInterfaceModel->ipv4s, $name);
                    $fullCommand .= $this->generateNetshIPv6Command($networkInterfaceModel->ipv6s, $name);
                    if (intval($networkInterfaceModel->type) === NetworkType::TYPE_PUBLIC_NETWORK) {
                        $fullCommand .= sprintf("netsh interface ip set dns \"%s\" static 1.1.1.1\r\n", $name);
                        $fullCommand .= sprintf("netsh interface ip add dns name=\"%s\" 8.8.8.8 index=2\r\n", $name);
                    }
                }
            }
        }

        return $fullCommand;
    }

    private function generateSetHostnameCommand($hostname)
    {
        return sprintf("wmic computersystem where name=\"%%COMPUTERNAME%%\" call rename name=\"%s\"\r\n", $hostname);
    }

    private function generateRebootCommand()
    {
        return "shutdown /r /t 0\r\n";
    }

    private function generateNetshIPv4Command($ipv4s, $interfaceName)
    {
        $fullCommand = "";
        foreach ($ipv4s as $ipv4) {
            $gateway = $ipv4->gateway;
            $command = sprintf("netsh interface ipv4 add address \"%s\" %s %s\r\n", $interfaceName, $ipv4->ip, \YunInternet\PHPIPCalculator\Constants::NETWORK_BITS_2_IP_NETMASK[$ipv4->pool_mask]);
            $fullCommand .= $command;
        }
        if (!empty($gateway)) {
            $fullCommand .= sprintf("route add -p 0.0.0.0 mask 0.0.0.0 %s\r\n", $gateway);
        }
        return $fullCommand;
    }

    private function generateNetshIPv6Command($ipv6s, $interfaceName)
    {
        $fullCommand = "";
        foreach ($ipv6s as $ipv6) {
            $gateway = $ipv6->gateway;
            $command = sprintf("netsh interface ipv6 add address \"%s\" %s/%d\r\n", $interfaceName, $ipv6->ip, $ipv6->pool_mask);
            $fullCommand .= $command;
        }
        if (!empty($gateway)) {
            $fullCommand .= sprintf("route add -p ::/0 %s\r\n", $gateway);
        }
        return $fullCommand;
    }
}