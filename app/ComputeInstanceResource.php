<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-28
 * Time: 下午9:37
 */

namespace App;


use App\Utils\ComputeInstanceUtils;
use App\Utils\Libvirt\LibvirtConnection;
use YunInternet\Libvirt\Domain;

/**
 * Class ComputeInstanceResource
 * Compute instance model, libvirt domain, utils getter
 * @package App
 */
class ComputeInstanceResource
{
    /**
     * @var string
     */
    private $uniqueId;

    /**
     * @var ComputeInstance
     */
    private $computeInstanceModel;

    /**
     * @var ComputeInstanceUtils
     */
    private $computeInstanceUtils;

    /**
     * @var Domain
     */
    private $libvirtDomain;

    public function __construct($idOrUniqueId)
    {
        $this->retrieveUniqueId($idOrUniqueId);
    }

    /**
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    /**
     * @return ComputeInstance
     */
    public function getComputeInstanceModel(): ComputeInstance
    {
        if (is_null($this->computeInstanceModel))
            $this->computeInstanceModel = ComputeInstance::query()->where("unique_id", $this->uniqueId)->firstOrFail();
        return $this->computeInstanceModel;
    }

    /**
     * @return Domain
     */
    public function getLibvirtDomain(): Domain
    {
        if (is_null($this->libvirtDomain))
            $this->libvirtDomain = LibvirtConnection::getConnection()->domainLookupByName($this->uniqueId);
        return $this->libvirtDomain;
    }

    /**
     * @return ComputeInstanceUtils
     */
    public function getComputeInstanceUtils(): ComputeInstanceUtils
    {
        if (is_null($this->computeInstanceUtils))
            $this->computeInstanceUtils = new ComputeInstanceUtils($this->getUniqueId());
        return $this->computeInstanceUtils;
    }

    /**
     * Get domain unique based on $idOrUniqueId
     * @param int|string $idOrUniqueId
     */
    private function retrieveUniqueId($idOrUniqueId)
    {
        if (is_numeric($idOrUniqueId)) {
            $this->computeInstanceModel = ComputeInstance::query()->findOrFail("id");
            $this->uniqueId = $this->computeInstanceModel->unique_id;
        } else {
            $this->uniqueId = $idOrUniqueId;
        }
    }
}