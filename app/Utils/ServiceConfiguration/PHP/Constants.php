<?php
/**
 * Created by PhpStorm.
 * Date: 19-1-30
 * Time: 下午6:53
 */

namespace App\Utils\ServiceConfiguration\PHP;


interface Constants
{
    const CONFIGURATION_FILE_PATH = "/etc/php/". PHP_MAJOR_VERSION . ".". PHP_MINOR_VERSION ."/fpm/pool.d/ccms-slave.conf";

    const INIT_SCRIPT_DEFAULT_FILE_PATH = "/etc/default/php-fpm" . PHP_MAJOR_VERSION . ".". PHP_MINOR_VERSION;

    const SYSTEMD_CONFIGURATION_FILE_PATH = "/etc/systemd/system/php" . PHP_MAJOR_VERSION . ".". PHP_MINOR_VERSION ."-fpm.service.d/override.conf";
}