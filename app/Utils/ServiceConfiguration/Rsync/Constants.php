<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-11
 * Time: 下午2:26
 */

namespace App\Utils\ServiceConfiguration\Rsync;


interface Constants
{
    const INIT_SCRIPT_DEFAULT_FILE_PATH = "/etc/default/rsync";

    const CONFIGURATION_FILE_PATH = "/etc/rsyncd.conf";

    const INCLUDE_DIRECTORY = "/etc/rsyncd.d";

    const SECRET_FILE_PATH = "/etc/rsyncd.secrets";

    const PUBLIC_IMAGE_CONFIGURATION_FILE_PATH = self::INCLUDE_DIRECTORY . "/ccms_public_images.conf";
}