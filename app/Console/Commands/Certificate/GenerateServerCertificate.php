<?php

namespace App\Console\Commands\Certificate;

use App\Constants\AvailableSystemConfigurations;
use App\IssuedCertificate;
use App\SystemConfigurations;
use App\Utils\Certificates\Certificates;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class GenerateServerCertificate extends Certificate
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cert:generate-server {--alt-name=*}' . self::OPTIONS;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate server certificate';

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
        if (!$this->shouldContinueIfCertExists())
            return 0;

        $privateKey = $this->generatePrivateKey();

        $dn = $this->retrieveDistinguishedName();
        $configFilePath = $this->createOpenSSLSANConfigFile($dn, $opensslConfigurationArray);

        try {
            $csr = openssl_csr_new($dn, $privateKey, $opensslConfigurationArray);
            $CACertificate = SystemConfigurations::value(AvailableSystemConfigurations::CA_CERTIFICATE);
            $x509 = openssl_csr_sign($csr, $CACertificate, Crypt::decryptString(SystemConfigurations::value(AvailableSystemConfigurations::CA_PRIVATE_KEY)), 3650, $opensslConfigurationArray, $serialNumber = $this->generateSerialNumber());

            openssl_pkey_export($privateKey, $exportedPrivateKey);
            openssl_x509_export($x509, $exportedX509);

            // IssuedCertificate::query()->where("name", "LIKE", "__ServerCertificate.%")->update(["status" => IssuedCertificate::STATUS_REVOKED, "revoke_time" => date("Y-m-d H:i:s")]);

            // Important: save certificate to database first
            IssuedCertificate::query()->create([
                "name" => "__ServerCertificate." . time(),
                "description" => "Certificate issue for server-side (libvirtd, nginx), \nDON'T UPDATE IT MANUALLY!",
                "certificate" => $exportedX509,
                "serial_number" => $serialNumber,
                "revoke_time" => date("Y-m-d H:i:s"),
                "status" => IssuedCertificate::STATUS_REVOKED,
            ]);

            // Update CRL
            $this->call("cert:generate-crl", ["--no-auto-restart-services" => true]);

            SystemConfigurations::setValue(AvailableSystemConfigurations::SERVER_PRIVATE_KEY, $exportedPrivateKey);
            Certificates::writeServerPrivateKey($exportedPrivateKey);

            SystemConfigurations::setValue(AvailableSystemConfigurations::SERVER_CERTIFICATE, $exportedX509);
            Certificates::writeServerCertificateFile($exportedX509);

            Certificates::writeFullChainServerCertificateFile($exportedX509, $CACertificate);
        } finally {
            unlink($configFilePath);
        }

        $this->warn("Server certificate generated, you need to restart the services that using it.");

        return 0;
    }

    private function shouldContinueIfCertExists()
    {
        if (SystemConfigurations::value(AvailableSystemConfigurations::SERVER_PRIVATE_KEY) && SystemConfigurations::value(AvailableSystemConfigurations::SERVER_CERTIFICATE)) {
            if (!$this->confirm('Server certificate and private key already exists, do you wish to overwrite them?'))
                return false;
        }

        return true;
    }
}
