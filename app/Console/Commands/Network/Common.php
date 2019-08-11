<?php
/**
 * Created by PhpStorm.
 * Date: 19-3-17
 * Time: 下午4:17
 */

namespace App\Console\Commands\Network;


use App\Utils\Libvirt\LibvirtConnection;
use YunInternet\Libvirt\Exception\LibvirtException;

abstract class Common
{
    public static function currentExistsNetwork($name, &$uuid = null, &$mac = null)
    {
        try {
            $network = LibvirtConnection::getConnection()->networkGet($name);
            $networkXMLElement = new \SimpleXMLElement($network->libvirt_network_get_xml_desc());
            $uuid = @$networkXMLElement->uuid->__toString();
            $mac = $networkXMLElement->mac["address"] ? $networkXMLElement->mac["address"]->__toString() : null;
        } catch (LibvirtException $libvirtException) {}
    }
}