<?php

namespace App\Console\Commands\Network;

use App\Utils\Libvirt\LibvirtConnection;
use Illuminate\Console\Command;
use YunInternet\CCMSCommon\Constants\Constants;
use YunInternet\Libvirt\Configuration\Network;
use YunInternet\Libvirt\Exception\LibvirtException;
use YunInternet\Libvirt\Libvirt;
use YunInternet\PHPIPCalculator\Calculator\IPv4;
use YunInternet\PHPIPCalculator\CalculatorFactory;

class DefineLocalPrivate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'network:define:local-private';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Define local private network';

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
        $networkConfiguration = new Network(Constants::DEFAULT_PRIVATE_NETWORK_NAME);
        $networkConfiguration->bridge()->setName(Constants::DEFAULT_LOCAL_PRIVATE_NETWORK_INTERFACE_NAME);
        $networkConfiguration->getSimpleXMLElement()->dns["enable"] = "no";

        $this->currentExistsNetwork($uuid, $mac);
        if ($uuid) {
            $this->info("Exists local private network uuid found: " . $uuid);
            $networkConfiguration->getSimpleXMLElement()->uuid = $uuid;
        }
        if ($mac) {
            $this->info("Exists local private network mac found: " . $mac);
            $networkConfiguration->getSimpleXMLElement()->mac["address"] = $mac;
        }

        $calculator = (new CalculatorFactory(Constants::DEFAULT_PRIVATE_NETWORK_IPV4_SUBNET))->create();
        $networkConfiguration->getSimpleXMLElement()->ip[0]["address"] = $calculator::calculable2HumanReadable($calculator->ipAt(1));
        $networkConfiguration->getSimpleXMLElement()->ip[0]["prefix "] = $calculator->getNetworkBits();

        $calculator = (new CalculatorFactory(Constants::DEFAULT_PRIVATE_NETWORK_IPV6_SUBNET))->create();
        $networkConfiguration->getSimpleXMLElement()->ip[1]["family"] = "ipv6";
        $networkConfiguration->getSimpleXMLElement()->ip[1]["address"] = $calculator->getFirstHumanReadableAddress();
        $networkConfiguration->getSimpleXMLElement()->ip[1]["prefix"] = $calculator->getNetworkBits();

        /*
        $networkConfiguration->getSimpleXMLElement()->ip->dhcp->range["start"] = $calculator::calculable2HumanReadable($calculator->ipAt(2));
        $networkConfiguration->getSimpleXMLElement()->ip->dhcp->range["end"] = $calculator::calculable2HumanReadable($calculator->ipAt(min($calculator->howMany() - 2, 65535)));
        */


        $network = LibvirtConnection::getConnection()->networkDefineXML($networkConfiguration->getXML());
        $this->info("Set autostart");
        $network->libvirt_network_set_autostart(1);

        $this->info("Local private network defined");
        return 0;
    }

    private function currentExistsNetwork(&$uuid = null, &$mac = null)
    {
        Common::currentExistsNetwork(Constants::DEFAULT_PRIVATE_NETWORK_NAME, $uuid, $mac);
    }
}
