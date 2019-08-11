<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-15
 * Time: 下午8:38
 */

namespace App\Utils\Libvirt;


use YunInternet\Libvirt\Connection;

class LibvirtConnection
{
    /**
     * @return Connection
     */
    public static function getConnection()
    {
        static $connection;
        if (is_null($connection))
            $connection = new Connection(static::getConnectionURI(), static::getUsername(), static::getPassword());
        return $connection;
    }

    public static function getConnectionURI()
    {
        return env("LIBVIRT_CONNECTION_URI", "qemu:///system");
    }

    public static function getUsername()
    {
        return env("LIBVIRT_USERNAME", null);
    }

    public static function getPassword()
    {
        return env("LIBVIRT_PASSWORD", null);
    }
}