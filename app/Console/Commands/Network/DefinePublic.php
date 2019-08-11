<?php

namespace App\Console\Commands\Network;

use App\Utils\Libvirt\LibvirtConnection;
use Illuminate\Console\Command;
use YunInternet\CCMSCommon\Constants\Constants;
use YunInternet\Libvirt\Configuration\Network;

class DefinePublic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'network:define:public';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Define default public network';

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
        $networkConfiguration = new Network(Constants::DEFAULT_PUBLIC_NETWORK_NAME);
        $networkConfiguration->forward()->setMode("bridge");
        $networkConfiguration->bridge()->setName(env("PUBLIC_NETWORK_BRIDGE_INTERFACE", "br0"));
        Common::currentExistsNetwork(Constants::DEFAULT_PUBLIC_NETWORK_NAME, $uuid);
        if (!empty($uuid))
            $networkConfiguration->getSimpleXMLElement()->uuid = $uuid;
        $network = LibvirtConnection::getConnection()->networkDefineXML($networkConfiguration->getXML());
        $network->libvirt_network_set_autostart(1);
        $this->info("Default public network defined");
        return 0;
    }
}
