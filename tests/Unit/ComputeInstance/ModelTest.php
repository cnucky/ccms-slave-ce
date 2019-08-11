<?php
/**
 * Created by PhpStorm.
 * Date: 19-3-29
 * Time: 下午2:56
 */

namespace Tests\Unit\ComputeInstance;


use App\ComputeInstance;
use Tests\TestCase;

class ModelTest extends TestCase
{
    public function testGetCPUUsage()
    {
        var_dump($this->getInstance()->cpuUsages()->get()->toArray());
        $this->assertTrue(true);
    }

    public function getInstance()
    {
        return ComputeInstance::query()->firstOrFail();
    }
}