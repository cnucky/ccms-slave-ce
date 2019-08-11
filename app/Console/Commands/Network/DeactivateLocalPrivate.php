<?php

namespace App\Console\Commands\Network;

use App\Utils\Libvirt\LibvirtConnection;
use Illuminate\Console\Command;
use YunInternet\CCMSCommon\Constants\Constants;

class DeactivateLocalPrivate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'network:deactivate:local-private';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate local private network';

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
        LibvirtConnection::getConnection()->networkGet(Constants::DEFAULT_PRIVATE_NETWORK_NAME)->libvirt_network_set_active(0);
        return 0;
    }
}
