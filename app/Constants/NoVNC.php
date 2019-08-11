<?php
/**
 * Created by PhpStorm.
 * Date: 19-4-14
 * Time: 下午1:11
 */

namespace App\Constants;


interface NoVNC
{
    const USER = "ccms-slave-noVNC";

    const INSTALLATION_PATH = "/var/www/ccms-slave/noVNC";

    const WEBSOCKET_SERVER_FILE_PATH = self::INSTALLATION_PATH . "/websockify-automatic";

    const CONSTANTS_FILE_PATH = self::INSTALLATION_PATH . "/constants.py";

    const CERTIFICATE_FILE_PATH = self::INSTALLATION_PATH . "/certificate/certificate.pem";

    const PRIVATE_KEY_FILE_PATH = self::INSTALLATION_PATH . "/certificate/privateKey.pem";
}