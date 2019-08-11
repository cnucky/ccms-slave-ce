<?php

namespace App\Console\Commands\Network;

use App\Utils\Libvirt\LibvirtConnection;
use Illuminate\Console\Command;
use YunInternet\CCMSCommon\Constants\Constants;

class DeactivateHostOnly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'network:deactivate:host-only';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate host-only network';

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
        LibvirtConnection::getConnection()->networkGet(Constants::DEFAULT_HOST_ONLY_NETWORK_NAME)->libvirt_network_set_active(0);
        $this->info("Host-only network deactivated.");
        return 0;
    }
}
