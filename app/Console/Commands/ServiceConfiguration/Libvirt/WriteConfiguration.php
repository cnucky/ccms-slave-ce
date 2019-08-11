<?php

namespace App\Console\Commands\ServiceConfiguration\Libvirt;

use App\Console\Commands\Certificate\Certificate;
use App\Constants\AvailableSystemConfigurations;
use App\IssuedCertificate;
use App\SystemConfigurations;
use App\Utils\Certificates\Certificates;
use App\Utils\ServiceConfiguration\Libvirt\LibvirtdConfiguration;
use App\Utils\ServiceConfiguration\Libvirt\LibvirtdInitScriptDefault;
use Illuminate\Console\Command;

class WriteConfiguration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'libvirt:write-conf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Write libvirtd configuration';

    private $libvirtdConfiguration;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(LibvirtdConfiguration $libvirtdConfiguration)
    {
        parent::__construct();

        $this->libvirtdConfiguration = $libvirtdConfiguration;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $listenTLS = intval(boolval(SystemConfigurations::value(AvailableSystemConfigurations::LIBVIRTD_LISTEN_TLS)));
        $listenTCP = intval(boolval(SystemConfigurations::value(AvailableSystemConfigurations::LIBVIRTD_LISTEN_TCP)));
        $TLSPort = SystemConfigurations::value(AvailableSystemConfigurations::LIBVIRTD_TLS_PORT);
        $TCPPort = SystemConfigurations::value(AvailableSystemConfigurations::LIBVIRTD_TCP_PORT);
        $listenAddress = SystemConfigurations::value(AvailableSystemConfigurations::LIBVIRTD_LISTEN_ADDRESS);
        $logLevel = SystemConfigurations::value(AvailableSystemConfigurations::LIBVIRTD_LOG_LEVEL);
        $logOutputs = SystemConfigurations::value(AvailableSystemConfigurations::LIBVIRTD_LOG_OUTPUTS);

        $keyFile = Certificates::SERVER_PRIVATE_KEY_FILE_PATH;
        $certFile = Certificates::SERVER_CERTIFICATE_FILE_PATH;
        $CAFile = Certificates::CA_CERTIFICATE_FILE_PATH;
        $CRLFile = Certificates::CA_CRL_FILE_PATH;

        $additionalConfiguration = SystemConfigurations::value(AvailableSystemConfigurations::LIBVIRTD_ADDITIONAL_CONFIGURATION);

        $this->libvirtdConfiguration
            ->setListenTLS($listenTLS)
            ->setListenTCP($listenTCP)
            ->setTLSPort($TLSPort)
            ->setTCPPort($TCPPort)
            ->setListenAddress($listenAddress)
            ->setLogLevel($logLevel)
            ->setLogOutputs($logOutputs)
            ->setServerKeyFilePath($keyFile)
            ->setServerCertificateFilePath($certFile)
            ->setCACertificateFilePath($CAFile)
            ->setCRLFilePath($CRLFile)
            ->setAdditionalContent($additionalConfiguration)
        ;

        // Only allow the certificates exists in issued certificates with normal status
        foreach (IssuedCertificate::query()->where("status", "=", IssuedCertificate::STATUS_NORMAL)->get(["name", "serial_number"]) as $certificate) {
            $this->libvirtdConfiguration->addAllowedDN(sprintf("CN=%s - %s", $certificate->name, Certificate::serialNumberEncode($certificate->serial_number)));
        }

        $this->libvirtdConfiguration->makeThenWrite();

        $this->info("libvirtd configuration updated");

        return 0;
    }
}
