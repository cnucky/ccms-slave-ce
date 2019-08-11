<?php

namespace App\Console\Commands\System\Configuration;

use App\Utils\ServiceConfiguration\Libvirt\Constants;
use Illuminate\Console\Command;

class Sudo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:config:sudo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure sudo for ccms-slave';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $libvirtdRestartCommand = Constants::RESTART_COMMAND;
        file_put_contents("/etc/sudoers.d/ccms-slave", <<<EOF
ccms-slave ALL=(ALL) NOPASSWD:/sbin/ebtables, /etc/init.d/nginx, {$libvirtdRestartCommand}, /etc/init.d/rsync, /usr/bin/supervisorctl restart ccms-slave-noVNC\:websockify-automatic

EOF
);
        $this->info("Configured.");
        return 0;
    }
}
