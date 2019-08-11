<?php
/**
 * Created by PhpStorm.
 * Date: 19-1-30
 * Time: 下午6:54
 */

namespace App\Utils\ServiceConfiguration\PHP;


class CCMSSlavePHPFPMPoolConfiguration
{
    public function make()
    {
        system("useradd -rMs /usr/sbin/nologin -d /var/www/ccms-slave-guest ccms-slave-guest");
        system("usermod -aG ccms-slave-guest www-data");
        system("useradd -rMs /usr/sbin/nologin ccms-slave");
        system("usermod -aG ccms-slave www-data");
        system("usermod -aG libvirt ccms-slave");

        return <<<EOF
[ccms-slave]
user = ccms-slave
group = ccms-slave

listen = /run/php/ccms-slave.sock

listen.owner = www-data
listen.group = www-data
listen.mode = 0600

pm = ondemand
pm.max_children = 64
pm.process_idle_timeout = 10s;
pm.max_requests = 256

access.log = /var/log/\$pool.access.log
access.format = "%R - %u %t \"%m %r%Q%q\" %s %f %{mili}d %{kilo}M %C%%"

slowlog = /var/log/\$pool.log.slow
request_slowlog_timeout = 1s

security.limit_extensions = .php

env[PATH] = /usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin

[ccms-slave-guest]
user = ccms-slave-guest
group = ccms-slave-guest

listen = /run/php/ccms-slave-guest.sock

listen.owner = www-data
listen.group = www-data
listen.mode = 0600

pm = ondemand
pm.max_children = 64
pm.process_idle_timeout = 10s;
pm.max_requests = 256

security.limit_extensions = .php
EOF
            ;
    }

    public function makeThenWrite()
    {
        file_put_contents(Constants::CONFIGURATION_FILE_PATH, $this->make());
    }
}