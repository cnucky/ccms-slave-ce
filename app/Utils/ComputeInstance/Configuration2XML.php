<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-15
 * Time: 下午8:46
 */

namespace App\Utils\ComputeInstance;


use App\Utils\ComputeInstance\XMLBuilder;
use App\Utils\Libvirt\LibvirtConnection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use YunInternet\Libvirt\Domain;
use YunInternet\Libvirt\Exception\ErrorCode;
use YunInternet\Libvirt\Exception\LibvirtException;

class Configuration2XML
{
    private $configuration;

    /**
     * ComputeInstance constructor.
     * @param array
     */
    public function __construct($configuration)
    {
        $this->configuration = $configuration;
    }

    public function generateXML($formatted = false)
    {
        $XMLBuilder = new XMLBuilder($this->configuration);
        return $XMLBuilder->getXML($formatted);
    }

    public function define(&$generatedXML = null, $formatted = false)
    {
        $generatedXML = $domainXML = $this->generateXML($formatted);
        // print $domainXML;
        return LibvirtConnection::getConnection()->domainDefineXML($domainXML);
    }

    private function listVolumes(Domain $domain)
    {
        $volumes = [];

        $domainXMLElement = new \SimpleXMLElement($domain->libvirt_domain_get_xml_desc(null));
        foreach ($domainXMLElement->devices->disk as $disk) {
            $type = @$disk["type"]->__toString();
            $device = @$disk["device"]->__toString();

            if ($type === "volume" && $device === "disk") {
                $pool = @$disk->source["pool"]->__toString();
                $volume = @$disk->source["volume"]->__toString();

                if (!empty($pool) && !empty($volume)) {
                    if (!array_key_exists($pool, $volumes))
                        $volumes[$pool] = [];
                    $volumes[$pool][] = $volume;
                }
            }
        }

        return $volumes;
    }
}