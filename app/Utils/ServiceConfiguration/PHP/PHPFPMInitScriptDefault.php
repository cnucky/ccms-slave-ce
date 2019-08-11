<?php
/**
 * Created by PhpStorm.
 * Date: 19-1-30
 * Time: 下午7:07
 */

namespace App\Utils\ServiceConfiguration\PHP;


class PHPFPMInitScriptDefault
{
    public function make()
    {
        return 'DAEMON_ARGS="${DAEMON_ARGS} -R"';
    }

    public function makeThenWrite()
    {
        file_put_contents(Constants::INIT_SCRIPT_DEFAULT_FILE_PATH, $this->make());
    }
}