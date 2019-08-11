<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-11
 * Time: 下午3:20
 */

namespace App\Utils\ServiceConfiguration\Rsync;


use App\Constants\AvailableSystemConfigurations;
use App\SystemConfigurations;

class RsyncSecret
{
    public function make()
    {
        $secrets = "";
        $publicImageSecret = SystemConfigurations::value(AvailableSystemConfigurations::PUBLIC_IMAGE_SECRET);
        if (@strlen($publicImageSecret))
            $secrets .= sprintf("%s:%s", \YunInternet\CCMSCommon\Constants\Constants::PUBLIC_IMAGE_AUTH_USER, $publicImageSecret);

        return $secrets;
    }

    public function makeThenWrite()
    {
        file_put_contents(Constants::SECRET_FILE_PATH, $this->make());
        chmod(Constants::SECRET_FILE_PATH, 0600);
    }
}