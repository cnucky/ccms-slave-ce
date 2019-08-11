<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-11
 * Time: 下午8:52
 */

namespace Tests\Unit;


use App\Utils\System\Memory;
use Tests\TestCase;

class MemoryInformationTest extends TestCase
{
    public function testMemoryInformationFormatter()
    {
        $memoryInformation = Memory::meminfoFormat();
        // var_dump($memoryInformation);

        $this->assertArrayHasKey("MemTotal", $memoryInformation);
        $this->assertTrue(is_numeric($memoryInformation["MemTotal"]));
    }

    public function testMemoryInformation()
    {
        $memoryInformation = Memory::memoryInformation();

        $this->assertArrayHasKey("total", $memoryInformation);
        $this->assertArrayHasKey("free", $memoryInformation);
        $this->assertArrayHasKey("available", $memoryInformation);

        $this->assertTrue(is_numeric($memoryInformation["total"]));
        $this->assertTrue(is_numeric($memoryInformation["free"]));
        $this->assertTrue(is_numeric($memoryInformation["available"]));
    }
}