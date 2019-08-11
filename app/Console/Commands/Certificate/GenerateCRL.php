<?php

namespace App\Console\Commands\Certificate;

use App\Constants\AvailableSystemConfigurations;
use App\IssuedCertificate;
use App\SystemConfigurations;
use App\Utils\Certificates\Certificates;
use App\Utils\ServiceConfiguration\Libvirt\Constants;
use Illuminate\Console\Command;
use Ukrbublik\openssl_x509_crl\X509;
use Ukrbublik\openssl_x509_crl\X509_CERT;
use Ukrbublik\openssl_x509_crl\X509_CRL;

class GenerateCRL extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cert:generate-crl {--no-auto-restart-services}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CRL';

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
        $privateKey = openssl_pkey_get_private(Certificates::getCAPrivateKey());
        $certificate = Certificates::getCACertificate();

        $crl = $this->generateCRL($privateKey, $certificate);
        $this->saveCACRL($crl);

        if ($this->option("no-auto-restart-services")) {
            $this->warn("CRL generated, remember to restart the services using this CRL (e.g. nginx, libvirtd).");
        } else {
            system("/usr/bin/sudo /etc/init.d/nginx reload");
            system("/usr/bin/sudo " . Constants::RESTART_COMMAND);
        }
        return 0;
    }

    private function revokedCertificates()
    {
        $revoked = [];

        $revokedCertificates = IssuedCertificate::query()->where("status", "=", IssuedCertificate::STATUS_REVOKED)->get();

        foreach ($revokedCertificates as $revokedCertificate) {
            /*
            $cert_data = X509::pem2der($revokedCertificate->certificate);
            $cert_root = X509_CERT::decode($cert_data);
            $cert = X509_CERT::parse($cert_root);

            $serialNumber = $cert["tbsCertificate"]["serialNumber"];
            */
            $serialNumber = $revokedCertificate->serial_number;

            // Invalid time value cause libvirtd fail to start
            $revokeDate = strtotime($revokedCertificate->revoke_time);
            if ($revokeDate === false)
                $revokeDate = time();

            $revoked[] = [
                'serial' => $serialNumber,
                'rev_date' => $revokeDate,
                'reason' => 0,
                'compr_date' => strtotime("-1 day"),
                'hold_instr' => null,
            ];
        }

        return $revoked;
    }

    private function generateCRL($privateKey, $exportedCertificate)
    {
        //Create CRL
        $ci = array(
            'no' => 1,
            'version' => 2,
            'days' => 3650,
            'alg' => OPENSSL_ALGO_SHA1,
            'revoked' => $this->revokedCertificates(),
        );

        $ca_pkey = $privateKey;
        $ca_cert = X509::pem2der($exportedCertificate);
        $crl_data = X509_CRL::create($ci, $ca_pkey, $ca_cert);

        return X509::der2pem4crl($crl_data);
    }

    private function saveCACRL($crl)
    {
        SystemConfigurations::setValue(AvailableSystemConfigurations::CA_CRL, $crl);
        Certificates::writeCACRLFile($crl);
    }
}
