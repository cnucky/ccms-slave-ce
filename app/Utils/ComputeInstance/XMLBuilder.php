<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-16
 * Time: 下午7:47
 */

namespace App\Utils\ComputeInstance;


use App\ComputeInstance;
use App\Constants\Storage;
use App\Utils\KeyCounter;
use App\Utils\System\Disk;
use YunInternet\CCMSCommon\Constants\MachineType;
use YunInternet\CCMSCommon\Constants\NetworkInterfaceModelCode;
use YunInternet\CCMSCommon\Constants\NetworkType;
use YunInternet\CCMSCommon\Constants\VolumeBusCode;
use YunInternet\Libvirt\Configuration\Domain;
use YunInternet\Libvirt\Constants\Constants;
use YunInternet\Libvirt\Contract\XMLElementContract;
use YunInternet\Libvirt\XMLImplement\SimpleXMLImplement;

class XMLBuilder
{
    private $configuration;

    private $busCounter;

    private $keyCounter;
    /**
     * @var Domain $builder
     */
    private $builder;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
        $this->busCounter = new BusCounter();
        $this->keyCounter = new KeyCounter();
    }

    public function getXML($formatted = false)
    {
        $this->builder = $this->generateBuilder();
        if (!empty($this->configuration["uuid"]))
            $this->builder->setUUID($this->configuration["uuid"]);
        $this->setMachineType();
        $this->setLoader();
        $this->setBlockIOTune();
        $this->setDisks();
        $this->setCDROM();
        $this->setFloppy();
        $this->setSCSIController();
        $this->setNetworkInterfaces();
        $this->setVNC();
        // $this->builder->cpu()->setMode("host-model");
        $this->builder->setOnCrash("restart");
        $this->builder->devices()
            ->addQEMUGuestAgentChannel()
            ->disableMemoryBalloon()
        ;

        $this->setSysinfo();

        $this->setHypervFeatureForWindows();
        $this->setTimerForWindows();

        if ($formatted)
            return $this->builder->getFormattedXML();
        return $this->builder->getXML();
    }

    private function generateBuilder()
    {
        return $domainXMLBuilder = new Domain($this->configuration["unique_id"], $this->configuration["memory"], $this->configuration["vCPU"]);
    }

    private function setMachineType()
    {
        $machineType = @MachineType::TYPE_CODE_2_TEXT[@$this->configuration["machine_type"]];
        if (is_null($machineType))
            $machineType = MachineType::TYPE_CODE_2_TEXT[MachineType::TYPE_Q35];

        $this->builder->os()->setMachine($machineType);
    }

    private function setLoader()
    {
        if ($this->configuration["use_legacy_bios"])
            return;
        $this->builder->os()->setLoader("/usr/share/ovmf/OVMF.fd");
        // $this->builder->os()->loader()->setAttribute("type", "pflash");
        $this->builder->os()->setLoaderReadonly(true);

        /*
        $this->builder->os()->nvram()->setValue("/var/lib/libvirt/qemu/nvram/guest_" . $this->configuration["unique_id"] . "_VARS.fd");
        $this->builder->os()->nvram()->setAttribute("template", "/usr/share/OVMF/OVMF_VARS.fd");
        */
    }

    private function setBlockIOTune()
    {
        if (@$this->configuration["io_weight"]) {
            $this->builder->blkiotune()->weight()->setValue(@$this->configuration["io_weight"]);
            foreach (Disk::getBlockDevices() as $blockDevice) {
                $this->builder->blkiotune()->addDevice($blockDevice, @$this->configuration["io_weight"], function (Domain\BlockIOTune\Device $device) {
                    if (@$this->configuration["read_bytes_sec"])
                        $device->read_bytes_sec()->setValue(@$this->configuration["read_bytes_sec"]);
                    if (@$this->configuration["write_bytes_sec"])
                        $device->write_bytes_sec()->setValue(@$this->configuration["write_bytes_sec"]);
                    if (@$this->configuration["read_iops_sec"])
                        $device->read_iops_sec()->setValue(@$this->configuration["read_iops_sec"]);
                    if (@$this->configuration["write_iops_sec"])
                        $device->write_iops_sec()->setValue(@$this->configuration["write_iops_sec"]);
                });
            }
        }
    }

    private function setDisks()
    {
        foreach (@$this->configuration["volumes"] as $volume) {
            /**
             * @var array $volume
             */
            $this->builder->devices()
                ->addDisk("volume", "disk", function (Domain\Device\Disk $disk) use ($volume) {
                    $uniqueId = $volume["unique_id"];
                    $bus = @VolumeBusCode::BUS_CODE_2_TEXT[$volume["bus"]];
                    if (empty($bus))
                        $bus = "virtio";

                    if ($bus === "ide") {
                        $disk->address()
                            ->setType("drive")
                            ->setController("0")
                            ->setUnit($this->busCounter->value($bus))
                        ;
                    } else if ($bus === "scsi") {
                        $disk->vendor()->setValue(Storage::DEFAULT_SCSI_VENDOR);
                        $disk->product()->setValue(Storage::DEFAULT_SCSI_PRODUCT);
                    }

                    $disk
                        ->volumeSource(Storage::storagePoolName(), $uniqueId)
                        ->setDriverType("qcow2")
                        ->setTargetBus($bus)
                        ->setTargetDevice($this->busCounter->formattedNameIncrease($bus))
                    ;

                    $this->keyCounter->increase($bus);

                    $disk->serial()->setValue($volume["unique_id"]);
                })
            ;
        }

        /*
        // Attach configuration volume
        $this->builder->devices()->addDisk("volume", "disk", function (Domain\Device\Disk $disk) use ($volume) {
            $disk
                ->volumeSource(Storage::DEFAULT_CONFIGURATION_STORAGE_POOL_NAME, $this->configuration["unique_id"])
                ->setDriverType("raw")
                ->setTargetBus("sata")
                ->setTargetDevice($this->busCounter->formattedNameIncrease("sata"))
            ;

            $disk->serial()->setValue($this->configuration["unique_id"]);
        });
        */
    }

    /**
     * Create scsi controller which model is virtio-scsi, by default, qemu does not use model virtio-scsi
     */
    private function setSCSIController()
    {
        $scsiDeviceCount = $this->keyCounter->value(VolumeBusCode::BUS_CODE_2_TEXT[VolumeBusCode::BUS_SCSI]);
        $controllerRequirements = DiskUtils::SCSIControllerRequirements($scsiDeviceCount);
        for ($i = 0; $i < $controllerRequirements; ++$i)
            $this->builder->devices()->addChild("controller", null, ["type" => "scsi", "model" => "virtio-scsi"]);
    }

    private function setCDROM()
    {
        $cdromBus = $this->configuration["machine_type"] === MachineType::TYPE_Q35 ? "sata" : "ide";

        foreach (@$this->configuration["cdroms"] as $cdrom) {
            $this->builder->devices()
                ->addDisk("file", "cdrom", function (Domain\Device\Disk $disk) use ($cdrom, $cdromBus) {
                    $disk
                        ->setDriverType("raw")
                        ->setTargetBus($cdromBus)
                        ->setTargetDevice($this->busCounter->formattedName($cdromBus))
                    ;

                    $disk->address()
                        ->setType("drive")
                        ->setController("0")
                        ->setUnit($this->busCounter->increase($cdromBus))
                    ;

                    if (!empty($cdrom["internalName"])) {
                        $disk
                            ->setType("volume")
                            ->volumeSource(\YunInternet\CCMSCommon\Constants\Constants::PUBLIC_ISO_STORAGE_POOL_NAME, $cdrom["internalName"])
                        ;
                    }
                })
            ;
        }
    }

    private function setFloppy()
    {
        foreach (@$this->configuration["floppies"] as $floppy) {
            $this->builder->devices()
                ->addDisk("file", "floppy", function (Domain\Device\Disk $disk) use ($floppy) {
                    $disk
                        ->setDriverType("raw")
                        ->setTargetBus("fdc")
                        ->setTargetDevice($this->busCounter->formattedNameIncrease("fdc"))
                        ->setReadonly(true)
                    ;

                    if (!empty($floppy["internalName"])) {
                        $disk
                            ->setType("volume")
                            ->volumeSource(\YunInternet\CCMSCommon\Constants\Constants::PUBLIC_FLOPPY_STORAGE_POOL_NAME, $floppy["internalName"])
                        ;
                    }
                })
            ;
        }
    }

    private function setNetworkInterfaces()
    {
        foreach (@$this->configuration["networkInterfaces"] as $networkInterface) {
            $model = NetworkInterfaceModelCode::MODEL_CODE_2_TEXT[$networkInterface["model"]];
            if (empty($model))
                $model = "virtio";

            if ($networkInterface["type"] == NetworkType::TYPE_PUBLIC_NETWORK) {
                $this->builder->devices()
                    ->addInterface("network", function (Domain\Device\InterfaceDevice $interfaceDevice) use ($model, $networkInterface) {
                        $interfaceDevice
                            ->setSourceNetwork(\YunInternet\CCMSCommon\Constants\Constants::DEFAULT_PUBLIC_NETWORK_NAME)
                            ->setModel($model)
                        ;

                        if (!empty($networkInterface["mac_address"]))
                            $interfaceDevice->setMacAddress($networkInterface["mac_address"]);

                        self::applyNWFilter($interfaceDevice, $networkInterface);
                        $this->applyQoS($interfaceDevice);
                    })
                ;
            } else {
                $this->builder->devices()
                    ->addInterface("network", function (Domain\Device\InterfaceDevice $interfaceDevice) use ($model, $networkInterface) {
                        $interfaceDevice
                            ->setSourceNetwork(\YunInternet\CCMSCommon\Constants\Constants::DEFAULT_PRIVATE_NETWORK_NAME)
                            ->setModel($model)
                        ;

                        if (!empty($networkInterface["mac_address"]))
                            $interfaceDevice->setMacAddress($networkInterface["mac_address"]);

                        self::applyNWFilter($interfaceDevice, $networkInterface);
                    })
                ;
            }
        }

        // Host-only network
        $this->builder->devices()
            ->addInterface("network", function (Domain\Device\InterfaceDevice $interfaceDevice) {
                $interfaceDevice
                    ->setSourceNetwork(\YunInternet\CCMSCommon\Constants\Constants::DEFAULT_HOST_ONLY_NETWORK_NAME)
                    ->setModel("virtio")
                    /*
                    ->applyNWFilter("ccms-host-only-clean-traffic", function (Domain\Device\InterfaceDevice\NWFilter $NWFilter) {
                        $NWFilter->addParameter("CTRL_IP_LEARNING", "dhcp");
                    })
                    */
                ;

                if (!empty($this->configuration["host_only_nic_mac_address"]))
                    $interfaceDevice->setMacAddress($this->configuration["host_only_nic_mac_address"]);
            })
        ;
    }

    public static function applyNWFilter(Domain\Device\InterfaceDevice $interfaceDevice, $networkInterfaceConfiguration)
    {
        $interfaceDevice
            ->applyNWFilter(env("NWFILTER_RULE", "ccms-clean-traffic"), function (Domain\Device\InterfaceDevice\NWFilter $NWFilter) use ($networkInterfaceConfiguration) {
                // Prevent error without any IPv4 or IPv6
                $NWFilter->addParameter("IP", "127.255.255.255");
                $NWFilter->addParameter("IPMASK", "32");
                $NWFilter->addParameter("IPV6", "::1");
                $NWFilter->addParameter("IPV6MASK", "128");

                foreach ($networkInterfaceConfiguration["ipv4Addresses"] as $ipv4Address) {
                    $NWFilter->addParameter("IP", $ipv4Address["ip"]);
                    $NWFilter->addParameter("IPMASK", $ipv4Address["mask"]);
                }

                foreach ($networkInterfaceConfiguration["ipv6Addresses"] as $ipv6Address) {
                    $NWFilter->addParameter("IPV6", $ipv6Address["ip"]);
                    $NWFilter->addParameter("IPV6MASK", $ipv6Address["mask"]);
                }
        });
    }

    private function applyQoS(Domain\Device\InterfaceDevice $interfaceDevice)
    {
        $bandwidth = $interfaceDevice->bandwidth();
        if (@$this->configuration["inbound_bandwidth"]) {
            $value = intdiv($this->configuration["inbound_bandwidth"], 8) * 1024;
            $bandwidth->setInboundAverage($value);
            $bandwidth->setInboundBurst($value);
            $bandwidth->setInboundPeak($value);
        }
        if (@$this->configuration["outbound_bandwidth"]) {
            $value = intdiv($this->configuration["outbound_bandwidth"], 8) * 1024;
            $bandwidth->setOutboundAverage($value);
            $bandwidth->setOutboundBurst($value);
            $bandwidth->setOutboundPeak($value);
        }
    }

    private function setVNC()
    {
        $this->builder->devices()
            ->useAbsoluteMousePointer()
            ->addVNCGraphic(function (Domain\Device\Graphic\VNCGraphic $VNCGraphic) {
                $vncPassword = @$this->configuration["vnc_password"];
                if (empty($vncPassword))
                    $vncPassword = substr(base64_encode(openssl_random_pseudo_bytes(8)), 0, 8);

                $VNCGraphic
                    ->setPassword($vncPassword)
                    ->useAutoPort()
                ;
            })
        ;
    }

    private function setSysinfo()
    {
        $vendor = env("SYSINFO_VENDOR", "Yun Internet Co., Ltd.");

        $this->builder->os()->setSMBIOSMode("sysinfo");

        /**
         * @var XMLElementContract $sysinfo
         */
        $sysinfo = $this->builder->sysinfo();

        $sysinfo->setAttribute("type", "smbios");

        $sysinfo->bios()->addChild("entry", $vendor, ["name" => "vendor"]);

        $sysinfo->system()->addChild("entry", $vendor, ["name" => "manufacturer"]);
        $sysinfo->system()->addChild("entry", "Compute Instance", ["name" => "product"]);
        // $sysinfo->system()->addChild("entry", "20190220", ["name" => "version"]);
        // $sysinfo->system()->addChild("entry", time() . mt_rand(10000, 99999), ["name" => "serial"]);
        $sysinfo->system()->addChild("entry", "$vendor - Compute Instance", ["name" => "family"]);

        $sysinfo->baseBoard()->addChild("entry", $vendor, ["name" => "manufacturer"]);
        $sysinfo->baseBoard()->addChild("entry", "Compute Instance", ["name" => "product"]);
        // $sysinfo->baseBoard()->addChild("entry", "20190220", ["name" => "version"]);
    }

    private function setHypervFeatureForWindows()
    {
        $this->builder->features()->addChild("hyperv", null, function (XMLElementContract $feature) {
            $feature
                ->createChild("relaxed", null, ["state" => "on"])
                ->createChild("vapic", null, ["state" => "on"])
                ->createChild("spinlocks", null, ["state" => "on", "retries" => "8191"])
                ->createChild("vpindex", null, ["state" => "on"])
                ->createChild("runtime", null, ["state" => "on"])
                ->createChild("synic", null, ["state" => "on"])
                ->createChild("stimer", null, ["state" => "on"])
                ->createChild("reset", null, ["state" => "on"])
                ->createChild("vendor_id", null, ["state" => "on", "value" => env("HYPERV_VENDOR_ID", "Y.NET")])
            ;
        });
    }

    private function setTimerForWindows()
    {
        $this->builder->clock()
            ->addTimer("hypervclock", function (SimpleXMLImplement $timer) {
                $timer->setAttribute("present", "yes");
            })
        ;
    }
}