<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-17
 * Time: 下午4:29
 */

namespace Tests\Unit;


use App\Utils\ComputeInstance\BusCounter;
use Tests\TestCase;

class BusCounterTest extends TestCase
{
    public function testBusCounter()
    {
        $busCounter = new BusCounter();

        $this->assertEquals(0, $busCounter->value("virtio"));
        $this->assertEquals("vda", $busCounter->formattedNameIncrease("virtio"));
        $this->assertEquals("vdb", $busCounter->formattedNameIncrease("virtio"));
        $this->assertEquals("vdc", $busCounter->formattedNameIncrease("virtio"));
        $this->assertEquals("vdd", $busCounter->formattedNameIncrease("virtio"));
        $this->assertEquals("vde", $busCounter->formattedNameIncrease("virtio"));
        $this->assertEquals("vdf", $busCounter->formattedNameIncrease("virtio"));
        $this->assertEquals("vdg", $busCounter->formattedNameIncrease("virtio"));
        $this->assertEquals("vdh", $busCounter->formattedNameIncrease("virtio"));
        $this->assertEquals("vdi", $busCounter->formattedNameIncrease("virtio"));
        $this->assertEquals("vdj", $busCounter->formattedNameIncrease("virtio"));
        $this->assertEquals("vdk", $busCounter->formattedNameIncrease("virtio"));
        $this->assertEquals("vdl", $busCounter->formattedNameIncrease("virtio"));
        $this->assertEquals("vdm", $busCounter->formattedNameIncrease("virtio"));

        $this->assertEquals("hda", $busCounter->formattedNameIncrease("ide"));
    }
}