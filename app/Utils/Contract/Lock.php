<?php
/**
 * Created by PhpStorm.
 * Date: 19-3-29
 * Time: 上午12:57
 */

namespace App\Utils\Contract;


use App\Exceptions\LockException;
use App\Exceptions\WouldBlockException;

interface Lock
{
    /**
     * @param bool $nonBlocking
     * @return $this
     * @throws LockException
     * @throws WouldBlockException
     */
    public function shared($nonBlocking = false);

    /**
     * @param bool $nonBlocking
     * @return $this
     * @throws LockException
     * @throws WouldBlockException
     */
    public function exclusive($nonBlocking = false);

    /**
     * @return $this
     */
    public function unlock();

    /**
     * @return void
     */
    public function clean();
}