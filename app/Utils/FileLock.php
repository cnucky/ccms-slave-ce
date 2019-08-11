<?php
/**
 * Created by PhpStorm.
 * Date: 19-3-29
 * Time: 上午12:28
 */

namespace App\Utils;


use App\Exceptions\LockException;
use App\Exceptions\WouldBlockException;
use App\Utils\Contract\Lock;

class FileLock implements Lock
{
    const LOCK_DIRECTORY = __DIR__ . "/../../locks/";

    private $name;

    private $fp;

    /**
     * FileLock constructor.
     * @param $name
     * @throws LockException
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->fp = fopen(self::LOCK_DIRECTORY . $name, "c");
        if ($this->fp === false)
            throw new LockException("Lock file open unsuccessfully.");
    }

    /**
     * @inheritdoc
     */
    public function shared($nonBlocking = false)
    {
        if (flock($this->fp, LOCK_SH | self::nonBlockingFlag($nonBlocking), $wouldblock) === false) {
            if ($wouldblock)
                throw new WouldBlockException();
            throw new LockException();
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function exclusive($nonBlocking = false)
    {
        if (flock($this->fp, LOCK_EX | self::nonBlockingFlag($nonBlocking), $wouldblock) === false) {
            if ($wouldblock)
                throw new WouldBlockException();
            throw new LockException();
        }
        return $this;
    }

    public function unlock()
    {
        flock($this->fp, LOCK_UN, $wouldblock);
        return $this;
    }

    public function clean()
    {
        fclose($this->fp);
    }

    private static function nonBlockingFlag($nonBlocking)
    {
        return $nonBlocking ? LOCK_NB : 0;
    }
}