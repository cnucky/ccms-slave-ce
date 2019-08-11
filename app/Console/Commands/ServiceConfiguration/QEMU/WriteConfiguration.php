<?php

namespace App\Console\Commands\ServiceConfiguration\QEMU;

use Illuminate\Console\Command;

class WriteConfiguration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qemu:write-conf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Write QEMU configuration.';

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
        if (file_exists("/etc/libvirt/qemu.conf"))
            rename("/etc/libvirt/qemu.conf", "/etc/libvirt/qemu.conf." . time());

        file_put_contents("/etc/libvirt/qemu.conf", <<<EOF
user = "root"
group = "root"
dynamic_ownership = 1
security_driver = "none"
EOF
);
        return 0;
    }
}
