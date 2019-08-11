<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-28
 * Time: 下午10:32
 */

namespace Tests\Unit\LibvirtDomain;


use App\Utils\Libvirt\LibvirtConnection;
use Tests\TestCase;

class LibvirtDomainTest extends TestCase
{
    public function testDomainCreate()
    {
        $this->getDomain()->libvirt_domain_create();
        $this->assertTrue(true);
    }

    public function testDomainReboot()
    {
        $this->getDomain()->libvirt_domain_reboot();
        $this->assertTrue(true);
    }

    public function testDomainReset()
    {
        $this->getDomain()->libvirt_domain_reset();
        $this->assertTrue(true);
    }

    public function testDomainDestroy()
    {
        $this->getDomain()->libvirt_domain_destroy();
        $this->assertTrue(true);
    }

    public function testDomainVNCDisplay()
    {
        var_dump($this->getDomain()->vncDisplay());
        $this->assertTrue(true);
    }

    public function testDomainGetNetworkInfo()
    {
        var_dump($this->getDomain()->libvirt_domain_get_network_info());
        $this->assertTrue(true);
    }

    public function testDomainBlockStats()
    {
        $domainBlockStats = $this->getDomain()->libvirt_domain_block_stats("");
        var_dump($domainBlockStats);
        $this->assertTrue(true);
    }

    public function testDomainCPUTotalStats()
    {
        $domainCPUStatus = $this->getDomain()->libvirt_domain_get_cpu_total_stats();
        var_dump($domainCPUStatus);
        $this->assertTrue(true);
    }

    /**
     * @return \YunInternet\Libvirt\Domain
     */
    public function getDomain()
    {
        return LibvirtConnection::getConnection()->domainLookupByName("ci-1pozap81qrof");
    }
}