<?php
/**
 * Created by PhpStorm.
 * Date: 19-1-30
 * Time: 下午10:48
 */

namespace App\Utils\ServiceConfiguration\PHP;


class PHPFPMSystemdConfiguration
{
    public function make()
    {
        $phpMajorMinorVersion = PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;
        return <<<EOF
[Service]
ExecStart=
ExecStart=/usr/sbin/php-fpm${phpMajorMinorVersion} -R --nodaemonize --fpm-config /etc/php/${phpMajorMinorVersion}/fpm/php-fpm.conf

EOF
            ;
    }

    public function makeThenWrite()
    {
        $parentDirectoryName = dirname(Constants::SYSTEMD_CONFIGURATION_FILE_PATH);
        if (!is_dir($parentDirectoryName))
            mkdir($parentDirectoryName);
        file_put_contents(Constants::SYSTEMD_CONFIGURATION_FILE_PATH, $this->make());
    }
}