<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-16
 * Time: 下午8:09
 */

namespace Tests\Unit;


use App\Utils\System\Disk;
use Tests\TestCase;

class DiskUtilsTest extends TestCase
{
    public function testGetBlockDevices()
    {
        var_dump(Disk::getBlockDevices());
        $this->assertTrue(true);
    }

    public function testFormatDiskName()
    {
        $this->assertEquals("a", Disk::formatDiskName(0));
        $this->assertEquals("sdz", Disk::formatDiskName(25, "sd"));
        $this->assertEquals("vdzz", Disk::formatDiskName(701, "vd"));
        $this->assertEquals("vdzzz", Disk::formatDiskName(18277, "vd"));
    }
}