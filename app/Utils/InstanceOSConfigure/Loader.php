<?php
/**
 * Created by PhpStorm.
 * Date: 19-3-23
 * Time: 下午4:03
 */

namespace App\Utils\InstanceOSConfigure;


use App\Utils\InstanceOSConfigure\Contract\Configurator;
use App\Utils\InstanceOSConfigure\Exception\ConfiguratorException;
use App\Utils\InstanceOSConfigure\Exception\ErrorCode;

class Loader
{
    /**
     * @var \ReflectionClass[]
     */
    private static $map = [];

    public static function load()
    {
        $directories = scandir(__DIR__ . "/Configurator/");
        array_shift($directories);
        array_shift($directories);

        $basicNamespace = __NAMESPACE__ . "\\Configurator";

        foreach ($directories as $directory) {
            $className = sprintf("%s\\%s\\%s", $basicNamespace, $directory, $directory);
            try {
                $reflectionClass = new \ReflectionClass($className);
                if ($reflectionClass->isSubclassOf(Configurator::class)) {
                    foreach ($reflectionClass->getConstant("SUPPORTED_OS_LIST") as $os) {
                        self::$map[$os] = $reflectionClass;
                    }
                }
            } catch (\ReflectionException $e) {
            }
        }
    }

    public static function isExists($os)
    {
        if (!count(self::$map))
            self::load();

        return array_key_exists($os, self::$map);
    }

    public static function existsOrFail($os)
    {
        if (!self::isExists($os))
            throw new ConfiguratorException("UNSUPPORTED_OS", ErrorCode::UNSUPPORTED_OS);
    }

    public static function get($os)
    {
        if (!count(self::$map))
            self::load();
        self::existsOrFail($os);
        return self::$map[$os];
    }

    /**
     * @param $os
     * @param mixed ...$arguments
     * @return Configurator
     */
    public static function newInstance($os, ... $arguments) : Configurator
    {
        if (!count(self::$map))
            self::load();
        self::existsOrFail($os);
        $reflectionClass = self::get($os);
        return $reflectionClass->newInstance($os, ... $arguments);
    }
}