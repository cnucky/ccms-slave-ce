<?php

namespace App\Console\Commands\ServiceConfiguration\NoVNC;

use App\Constants\NoVNC;
use Illuminate\Console\Command;

class Supervisor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'noVNC:write-supervisor-conf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Write noVNC supervisor configuration.';

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
        $user = NoVNC::USER;
        system("useradd -rMs /usr/sbin/nologin $user");
        system("usermod -aG ccms-slave $user");
        $command = NoVNC::WEBSOCKET_SERVER_FILE_PATH;
        file_put_contents("/etc/supervisor/conf.d/ccms-slave-noVNC.conf", <<<EOF
[program:ccms-slave-noVNC]
process_name=websockify-automatic
command=$command
autostart=true
autorestart=true
user=$user
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/websockify-automatic.log
EOF
        );
        system("/etc/init.d/supervisor restart");
        $this->info("Configuration updated.");
        return 0;
    }
}
