<?php
/**
 * Created by PhpStorm.
 * Date: 19-3-29
 * Time: 上午12:56
 */

namespace App\Utils;

use App\Utils\Contract\Lock;

class LockFactory
{
    /**
     * @param $name
     * @return Lock
     * @throws \App\Exceptions\LockException
     */
    public static function getLocker($name)
    {
        return new FileLock($name);
    }
}