<?php
/**
 * Created by PhpStorm.
 * Date: 19-3-23
 * Time: 下午4:05
 */

namespace Tests\Unit\OSConfigurator;


use App\Utils\InstanceOSConfigure\Loader;
use Tests\TestCase;

class LoaderTest extends TestCase
{
    public function testLoad()
    {
        var_dump(Loader::load());
        $this->assertTrue(true);
    }
}