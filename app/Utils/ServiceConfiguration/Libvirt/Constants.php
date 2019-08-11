<?php
/**
 * Created by PhpStorm.
 * Date: 19-1-30
 * Time: 下午5:51
 */

namespace App\Utils\ServiceConfiguration\Libvirt;


interface Constants
{
    const CONFIGURATION_FILE_PATH = __DIR__ . "/../../../../storage/conf/libvirt/libvirtd.conf";

    const INIT_SCRIPT_DEFAULT_FILE_PATH = "/etc/default/libvirtd";

    const SYSCONF_FILE_PATH = "/etc/sysconfig/libvirtd";

    const RESTART_COMMAND = "/bin/systemctl restart libvirtd";
}