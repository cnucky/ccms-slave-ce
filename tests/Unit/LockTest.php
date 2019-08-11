<?php
/**
 * Created by PhpStorm.
 * Date: 19-3-29
 * Time: 上午12:53
 */

namespace Tests\Unit;


use App\Utils\FileLock;
use Tests\TestCase;

class LockTest extends TestCase
{
    public function testLock()
    {
        $fileLock = new FileLock("test");
        $fileLock->exclusive(true);
        $fileLock->unlock();
        $fileLock->clean();
        $this->assertTrue(true);
    }
}