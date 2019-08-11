<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-17
 * Time: 上午2:26
 */

namespace App\Utils;


class KeyCounter
{
    private $keyCounters = [];

    public function value($key)
    {
        $this->initValue($key);
        return $this->keyCounters[$key];
    }

    public function increase($key)
    {
        $this->initValue($key);
        return $this->keyCounters[$key]++;
    }

    private function initValue($key)
    {
        if (!array_key_exists($key, $this->keyCounters))
            $this->keyCounters[$key] = 0;
        return $this->keyCounters;
    }
}