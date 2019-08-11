<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-11
 * Time: 下午2:45
 */

namespace App\Utils\ServiceConfiguration\Rsync;

use YunInternet\CCMSCommon\Constants\Constants as CCMSConstants;

class RsyncConfiguration
{
    public function makeConfiguration()
    {
        $secretFilePath = Constants::SECRET_FILE_PATH;

        return <<<EOF
lock file = /var/run/rsync.lock
log file = /var/log/rsync.log
pid file = /var/run/rsync.pid
secrets file = $secretFilePath

&include /etc/rsyncd.d
EOF;
    }

    public function makePublicImageConfiguration()
    {
        $publicImageName = CCMSConstants::PUBLIC_IMAGE_NAME;
        $publicImageDirectory = CCMSConstants::PUBLIC_IMAGE_DIRECTORY;
        $publicImageAuthUser = CCMSConstants::PUBLIC_IMAGE_AUTH_USER;

        return <<<EOF
[$publicImageName]
    path = $publicImageDirectory/./
    comment = CCMS Virt public images
    read only = yes
    list = no
    auth users = $publicImageAuthUser
EOF;

    }

    public function makeThenWrite()
    {
        if (!is_dir(Constants::INCLUDE_DIRECTORY))
            mkdir(Constants::INCLUDE_DIRECTORY);
        file_put_contents(Constants::CONFIGURATION_FILE_PATH, $this->makeConfiguration());
        file_put_contents(Constants::PUBLIC_IMAGE_CONFIGURATION_FILE_PATH, $this->makePublicImageConfiguration());
    }
}