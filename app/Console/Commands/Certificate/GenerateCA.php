<?php

namespace App\Console\Commands\Certificate;

use App\Constants\AvailableSystemConfigurations;
use App\IssuedCertificate;
use App\SystemConfigurations;
use App\Utils\Certificates\Certificates;
use Illuminate\Support\Facades\Crypt;
use Ukrbublik\openssl_x509_crl\X509;
use Ukrbublik\openssl_x509_crl\X509_CRL;

class GenerateCA extends Certificate
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cert:generate-ca' . self::OPTIONS;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CA certificate';

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
     * @throws \Exception
     */
    public function handle()
    {
        X509::checkServer();

        if (!$this->shouldContinueIfCertExists())
            return 0;

        $privateKey = $this->generatePrivateKey();
        $csr = $this->generateCSR($privateKey);
        $x509 = $this->csrSign($csr, $privateKey);

        openssl_pkey_export($privateKey, $exportedPrivateKey);
        openssl_x509_export($x509, $exportedX509);

        $this->saveCAPrivateKey($exportedPrivateKey);
        $this->saveCACertificate($exportedX509);

        IssuedCertificate::query()->delete();

        $this->info("CA Certificate fingerprint: " . openssl_x509_fingerprint($x509));

        return 0;
    }

    private function shouldContinueIfCertExists()
    {
        if (SystemConfigurations::value(AvailableSystemConfigurations::CA_CERTIFICATE)) {
            if (!$this->confirm('A CA certificate already exists, do you wish to overwrite it?'))
                return false;
        }

        return true;
    }

    private function generateCSR($privateKey, &$dn = null)
    {
        $dn = $this->retrieveDistinguishedName();

        return openssl_csr_new($dn, $privateKey, self::COMMON_OPENSSL_CONFIGURATIONS);
    }

    private function csrSign($csr, $privateKey)
    {
        return openssl_csr_sign($csr, null, $privateKey, 3650, self::COMMON_OPENSSL_CONFIGURATIONS);
    }


    private function saveCAPrivateKey($exportedPrivateKey)
    {
        SystemConfigurations::setValue(AvailableSystemConfigurations::CA_PRIVATE_KEY, Crypt::encryptString($exportedPrivateKey));
    }

    private function saveCACertificate($exportedCACertificate)
    {
        SystemConfigurations::setValue(AvailableSystemConfigurations::CA_CERTIFICATE, $exportedCACertificate);
        Certificates::writeCACertificate($exportedCACertificate);
    }
}
