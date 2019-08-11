<?php

namespace App\Console\Commands\ServiceConfiguration\NWFilter;

use App\Utils\Libvirt\LibvirtConnection;
use Illuminate\Console\Command;
use YunInternet\CCMSCommon\Constants\Constants;
use YunInternet\Libvirt\Exception\LibvirtException;
use YunInternet\PHPIPCalculator\CalculatorFactory;

class Write extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nwfilter:write';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Write NWFilter rules';

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

        $uuid = $this->nwFilterUUID("ccms-no-mac-spoofing");
        LibvirtConnection::getConnection()->libvirt_nwfilter_define_xml(<<<EOF
<filter name='ccms-no-mac-spoofing' chain='mac' priority='-800'>
  $uuid
  <rule action='return' direction='out' priority='500'>
    <mac srcmacaddr='\$MAC'/>
  </rule>
  <rule action='return' direction='in' priority='500'>
    <mac dstmacaddr='\$MAC'/>
  </rule>
  <rule action='drop' direction='out' priority='500'>
    <mac/>
  </rule>
</filter>
EOF
        );

        $uuid = $this->nwFilterUUID("ccms-allow-dhcp-client");
        LibvirtConnection::getConnection()->libvirt_nwfilter_define_xml(<<<EOF
<filter name='ccms-allow-dhcp-client' chain='ipv4-dhcp' priority='-720'>
  $uuid
  <rule action='accept' direction='out' priority='100'>
    <ip srcipaddr='0.0.0.0' dstipaddr='255.255.255.255' protocol='udp' srcportstart='68' dstportstart='67'/>
  </rule>
  <rule action='accept' direction='in' priority='100'>
    <ip protocol='udp' srcportstart='67' dstportstart='68'/>
  </rule>
</filter>
EOF
        );

        $uuid = $this->nwFilterUUID("ccms-host-only-no-ip-spoofing");
        LibvirtConnection::getConnection()->libvirt_nwfilter_define_xml(<<<EOF
<filter name='ccms-host-only-no-ip-spoofing' chain='ipv4-ip' priority='-710'>
  $uuid
  <rule action='return' direction='out' priority='500'>
    <ip srcipaddr='\$IP'/>
  </rule>
  <rule action='drop' direction='out' priority='1000'/>
</filter>
EOF
        );

        $uuid = $this->nwFilterUUID("ccms-no-ip-spoofing");
        LibvirtConnection::getConnection()->libvirt_nwfilter_define_xml(<<<EOF
<filter name='ccms-no-ip-spoofing' chain='ipv4-ip' priority='-710'>
  $uuid
  <rule action='drop' direction='out' priority='499'>
    <ip srcipaddr='127.0.0.0' srcipmask='8'/>
  </rule>

  <rule action='return' direction='out' priority='500'>
    <ip srcipaddr='\$IP' srcipmask='\$IPMASK'/>
  </rule>
  <rule action='drop' direction='out' priority='1000'/>
</filter>
EOF
        );

        $uuid = $this->nwFilterUUID("ccms-no-ipv6-spoofing");
        LibvirtConnection::getConnection()->libvirt_nwfilter_define_xml(<<<EOF
<filter name='ccms-no-ipv6-spoofing' chain='ipv6-ip' priority='-710'>
  $uuid
  <rule action='drop' direction='out' priority='499'>
    <ipv6 srcipaddr='::1' srcipmask='128'/>
  </rule>
  <rule action='return' direction='out' priority='500'>
    <ipv6 srcipaddr='\$IPV6' srcipmask='\$IPV6MASK'/>
  </rule>
  <rule action='drop' direction='out' priority='1000'/>
</filter>
EOF
        );

        $uuid = $this->nwFilterUUID("ccms-no-dhcp-server");
        LibvirtConnection::getConnection()->libvirt_nwfilter_define_xml(<<<EOF
<filter name='ccms-no-dhcp-server' chain='ipv4-dhcp' priority='-720'>
  $uuid
  <rule action='drop' direction='out' priority='100'>
    <ip protocol='udp' srcportstart='67'/>
  </rule>
  <rule action='drop' direction='in' priority='100'>
    <ip protocol='udp' dstportstart='67'/>
  </rule>
  <rule action='return' direction='out' priority='500'/>
</filter>
EOF
        );

        $uuid = $this->nwFilterUUID("ccms-no-router-advertisement");
        LibvirtConnection::getConnection()->libvirt_nwfilter_define_xml(<<<EOF
<filter name='ccms-no-router-advertisement' chain='ipv6-icmp' priority='-720'>
  $uuid
  <rule action='drop' direction='out' priority='100'>
    <ipv6 protocol='icmpv6' type='134' code='0' codeend='255'/>
  </rule>
  <rule action='return' direction='out' priority='500'/>
</filter>
EOF
        );

        $uuid = $this->nwFilterUUID("ccms-allow-incoming-ipv4");
        LibvirtConnection::getConnection()->libvirt_nwfilter_define_xml(<<<EOF
<filter name='ccms-allow-incoming-ipv4' chain='ipv4' priority='-720'>
  $uuid
  <rule action='accept' direction='in' priority='500'/>
</filter>
EOF
        );

        $uuid = $this->nwFilterUUID("ccms-allow-incoming-ipv6");
        LibvirtConnection::getConnection()->libvirt_nwfilter_define_xml(<<<EOF
<filter name='ccms-allow-incoming-ipv6' chain='ipv6' priority='-720'>
  $uuid
  <rule action='accept' direction='in' priority='500'/>
</filter>
EOF
        );

        $uuid = $this->nwFilterUUID("ccms-no-arp-ip-spoofing");
        LibvirtConnection::getConnection()->libvirt_nwfilter_define_xml(<<<EOF
<filter name='ccms-no-arp-ip-spoofing' chain='arp-ip' priority='-510'>
  $uuid
  <rule action='return' direction='out' priority='400'>
    <arp arpsrcipaddr='\$IP' arpsrcipmask='\$IPMASK'/>
  </rule>
  <rule action='drop' direction='out' priority='1000'/>
</filter>
EOF
        );

        $uuid = $this->nwFilterUUID("ccms-no-arp-spoofing");
        LibvirtConnection::getConnection()->libvirt_nwfilter_define_xml(<<<EOF
<filter name='ccms-no-arp-spoofing' chain='root'>
  $uuid
  <filterref filter='no-arp-mac-spoofing'/>
  <filterref filter='ccms-no-arp-ip-spoofing'/>
</filter>
EOF
        );

        $uuid = $this->nwFilterUUID("ccms-clean-traffic");
        LibvirtConnection::getConnection()->libvirt_nwfilter_define_xml(<<<EOF
<filter name='ccms-clean-traffic' chain='root'>
  $uuid
  <filterref filter='ccms-no-mac-spoofing'/>
  <filterref filter='ccms-no-ip-spoofing'/>
  <filterref filter='ccms-no-ipv6-spoofing'/>
  <filterref filter='ccms-no-dhcp-server'/>
  <filterref filter='ccms-no-router-advertisement'/>
  <rule action='accept' direction='out' priority='-650'>
    <mac protocolid='ipv4'/>
  </rule>
  <rule action='accept' direction='out' priority='-650'>
    <mac protocolid='ipv6'/>
  </rule>
  <filterref filter='ccms-allow-incoming-ipv4'/>
  <filterref filter='ccms-allow-incoming-ipv6'/>
  <filterref filter='ccms-no-arp-spoofing'/>
  <rule action='accept' direction='inout' priority='-500'>
    <mac protocolid='arp'/>
  </rule>
  <filterref filter='no-other-l2-traffic'/>
  <filterref filter='qemu-announce-self'/>
</filter>
EOF
        );


        $hostOnlyNetworkXMLElement = new \SimpleXMLElement(LibvirtConnection::getConnection()->networkGet(Constants::DEFAULT_HOST_ONLY_NETWORK_NAME)->libvirt_network_get_xml_desc());
        $nodeHostOnlyNetworkIP = $hostOnlyNetworkXMLElement->ip["address"]->__toString();

        $uuid = $this->nwFilterUUID("ccms-host-only-clean-traffic");
        LibvirtConnection::getConnection()->libvirt_nwfilter_define_xml(<<<EOF
<filter name='ccms-host-only-clean-traffic' chain='root'>
  $uuid
  <filterref filter='ccms-no-mac-spoofing'/>
  <filterref filter='ccms-host-only-no-ip-spoofing'/>
  <filterref filter='ccms-allow-dhcp-client'/>
  <filterref filter='ccms-no-dhcp-server'/>
  <filterref filter='ccms-no-router-advertisement'/>
  <rule action='accept' direction='out' priority='-650'>
    <ip protocol='tcp' dstipaddr='$nodeHostOnlyNetworkIP' dstportstart='2050'/>
  </rule>
  <filterref filter='ccms-allow-incoming-ipv4'/>
  <filterref filter='no-arp-spoofing'/>
  <rule action='accept' direction='inout' priority='-500'>
    <mac protocolid='arp'/>
  </rule>
  <filterref filter='no-other-l2-traffic'/>
  <filterref filter='qemu-announce-self'/>
</filter>
EOF
        );


        return 0;
    }

    private function nwFilterUUID($name)
    {
        $uuid = "";
        try {
            $uuid = sprintf("<uuid>%s</uuid>", LibvirtConnection::getConnection()->nwFilterLookupByName($name)->libvirt_nwfilter_get_uuid_string());
        } catch (LibvirtException $libvirtException) {
        }
        return $uuid;
    }
}
