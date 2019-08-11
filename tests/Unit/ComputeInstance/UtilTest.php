<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-17
 * Time: 下午1:46
 */

namespace Tests\Unit\ComputeInstance;


use App\ComputeInstance;
use App\IssuedCertificate;
use App\MasterServer;
use App\Utils\ComputeInstanceUtils;
use Tests\TestCase;
use YunInternet\CCMSCommon\NOVNC\Authenticator;

class UtilTest extends TestCase
{
    public function testDelete()
    {
        $this->getUtils()->delete(true);
        try {
            ComputeInstance::query()->where("unique_id", $this->getUtils()->getUniqueId())->firstOrFail()->delete();
        } catch (\Exception $e) {
        }
        $this->assertTrue(true);
    }

    public function testListCDROMs()
    {
        foreach ($this->getUtils()->getDiskUtils()->listCDROMs() as $cdrom) {
            $cdrom["type"] = "volume";
            $cdrom->source["pool"] = "ccms-public-isos";
            $cdrom->source["volume"] = "volume";
            print $cdrom->asXML();
            print PHP_EOL;
            print PHP_EOL;
        }

        $this->assertTrue(true);
    }

    public function testChangeCDROMMedia()
    {
        $this->getUtils()->getDiskUtils()->changeCDROMMedia(0, new ComputeInstance\Device\Disk\Source\VolumeSource("ccms-public-isos", "debian-9.8.0-amd64-netinst.iso"));
        $this->assertTrue(true);
    }

    public function testEjectCDROMMedia()
    {
        $this->getUtils()->getDiskUtils()->changeCDROMMedia(0);
        $this->assertTrue(true);
    }

    public function testSetVNCPassword()
    {
        $this->assertTrue($this->getUtils()->setVNCPassword("1234567899999"));
    }

    public function testAllDomains()
    {
        foreach (ComputeInstanceUtils::all() as $computeInstanceUtil) {
            print $computeInstanceUtil->getUniqueId();
            print PHP_EOL;
        }

        $this->assertTrue(true);
    }

    public function testNOVNCSignature()
    {
        $id = "ci-1po09892k1vw";
        $serial = "155118654141373";
        $salt = "833650546";
        $expire_at = "1552063298";
        $signature = "ojh+kqsdMtdZXG9OU5QXp5l+HSqn7FkRh7sK3ZqobpqfiRfJfO2dFpnUhW0O3Ip2EgVC7vs2kWQNQTN0+fjzgEJIxp908U60wSq0mQYFeExpIZRg404MTivXl1Lx1/FpzElmPv92I7rhur5XTPEb3kM1m7xcVRW7taPT5rFz47GckG+CqIfOTrTL+jfzho5kc5JG5BmkwgKS+w6TiZKxWzClEbvjWrJYbpDuJKLYVZqRH0PQe9ayh8cQB4eqTOEW7cDCLwOCs39PE0gNBPSDH4ZxisTaFqCzV1dF/n1ny9BaY1h9oNGYxvLhHbIXEm8y0GLWajeBbi5t34tE/k34TQ==";

        $certificate = IssuedCertificate::query()->where("serial_number", $serial)->firstOrFail();

        $this->assertTrue(Authenticator::verify($id, $salt, $expire_at, $serial, $signature, $certificate->certificate));
    }

    private $utils;

    public function getUtils()
    {
        if (is_null($this->utils))
            $this->utils = new ComputeInstanceUtils("ci-1pnsuf931f1g");
        return $this->utils;
    }
}