<?php
/**
 * Created by PhpStorm.
 * Date: 19-3-23
 * Time: 下午2:55
 */

namespace App\Utils\InstanceOSConfigure\Contract;


use App\ComputeInstance;
use App\ComputeInstanceConfigurationLog;
use App\Utils\InstanceOSConfigure\Exception\ConfiguratorException;
use App\Utils\InstanceOSConfigure\Exception\ErrorCode;
use App\Utils\Libvirt\LibvirtConnection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use YunInternet\Libvirt\Exception\LibvirtException;
use YunInternet\Libvirt\GuestAgent;

abstract class Configurator
{
    const SUPPORTED_OS_LIST = [];

    private $os;

    private $uniqueId;

    private $computeInstance;

    private $libvirtDomain;

    private $guestAgent;

    private $latestConfiguration;

    /**
     * Configurator constructor.
     * @param string $os
     * @param string|ComputeInstance $uniqueIdOrComputeInstance
     * @param null|ComputeInstance ComputeInstance|null $computeInstance
     * @throws ConfiguratorException
     */
    public function __construct($os, $uniqueIdOrComputeInstance, ComputeInstance $computeInstance = null)
    {
        $this->os = $os;

        if (is_string($uniqueIdOrComputeInstance)) {
            $this->uniqueId = $uniqueIdOrComputeInstance;
            $this->computeInstance = $computeInstance;
        } else if ($uniqueIdOrComputeInstance instanceof ComputeInstance) {
            $this->uniqueId = $uniqueIdOrComputeInstance->unique_id;
            $this->computeInstance = $uniqueIdOrComputeInstance;
        } else {
            throw new ConfiguratorException("Invalid constructor arguments", ErrorCode::INVALID_CONSTRUCTOR_ARGUMENT);
        }
    }

    /**
     * @return void
     * @throws ConfiguratorException
     */
    abstract public function firstBoot();

    /**
     * @return void
     * @throws ConfiguratorException
     */
    abstract public function configureNetwork();

    /**
     * @return void
     * @throws ConfiguratorException
     */
    abstract public function setPassword($plaintextPassword);

    /**
     * @return void
     * @throws ConfiguratorException
     */
    abstract public function setHostname($hostname);

    /**
     * @return string
     */
    public function getOs(): string
    {
        return $this->os;
    }

    /**
     * @return mixed
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * @return ComputeInstance
     * @throws ModelNotFoundException
     */
    public function getComputeInstance() : ComputeInstance
    {
        if (is_null($this->computeInstance)) {
            $this->computeInstance = ComputeInstance::query()->where("unique_id", $this->getUniqueId())->firstOrFail();
        }
        return $this->computeInstance;
    }


    /**
     * @return \YunInternet\Libvirt\Domain
     */
    public function getLibvirtDomain()
    {
        if (is_null($this->libvirtDomain)) {
            $this->libvirtDomain = LibvirtConnection::getConnection()->domainLookupByName($this->getUniqueId());
        }
        return $this->libvirtDomain;
    }


    /**
     * @return GuestAgent
     */
    public function getGuestAgent()
    {
        if (is_null($this->guestAgent)) {
            $this->guestAgent = new GuestAgent($this->getLibvirtDomain());
        }
        return $this->guestAgent;
    }

    /**
     * @return array
     * @throws ModelNotFoundException|ConfiguratorException
     */
    public function getLatestConfiguration()
    {
        if (is_null($this->latestConfiguration)) {
            $decodedResult = json_decode($this->getComputeInstance()->configurationLogs()->orderByDesc("id")->firstOrFail()->configuration, true);
            if (is_null($decodedResult))
                throw new ConfiguratorException(json_last_error_msg(), ErrorCode::GUEST_AGENT_RESPONSE_DECODE_UNSUCCESSFULLY);
            $this->latestConfiguration = $decodedResult;
        }
        return $this->latestConfiguration;
    }

    /**
     * @return string
     * @throws ConfiguratorException
     */
    public function getLatestPlaintextPassword()
    {
        return $this->getLatestConfiguration()["password"];
    }

    /**
     * @return string
     * @throws ConfiguratorException
     */
    public function getLatestHostname()
    {
        return $this->getLatestConfiguration()["hostname"];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNetworkInterfaceModelKeyByMac()
    {
        return $this->getComputeInstance()->networkInterfaces()->get()->keyBy("mac");
    }

    /**
     * @return array
     * @throws LibvirtException
     */
    public function getGuestNetworkInterfaces()
    {
        return $this->getGuestAgent()->getNetworkInterfaces()["return"];
    }
}