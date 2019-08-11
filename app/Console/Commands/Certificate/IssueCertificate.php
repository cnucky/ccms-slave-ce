<?php

namespace App\Console\Commands\Certificate;

use App\Constants\AvailableSystemConfigurations;
use App\IssuedCertificate;
use App\SystemConfigurations;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class IssueCertificate extends Certificate
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cert:issue {name : Certificate name} {description? : Description for the certificate} {--days=365} {--save-full-chain-cert-to= : Save your full chain certificate to specific location} {--save-pkcs12-to=} {--pkcs12-password=} {--save-key-to=} {--save-cert-to=} {--country-name=} {--state-or-province-name=} {--locality-name=} {--organization-name=} {--organizational-unit-name=} {--email-address=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Issue a certificate signed by CA';

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
        if (IssuedCertificate::query()->where("name", "=", $name = $this->argument("name"))->first()) {
            $this->error("Name '$name' already used by another certificate.");
            return 1;
        }

        $notAllowPrefix = "__ServerCertificate.";

        if (strstr($name, $notAllowPrefix) === "") {
            $this->error("Certificate name with prefix '$notAllowPrefix' isn't allowed.");
            return 1;
        }

        $serialNumber = $this->generateSerialNumber();

        // Set for libvirtd authenticate user
        $dn = ["commonName" => $name . " - " . parent::serialNumberEncode($serialNumber)];

        $this->line("");

        $CAPrivateKey = Crypt::decryptString(SystemConfigurations::value(AvailableSystemConfigurations::CA_PRIVATE_KEY));
        $CACert = SystemConfigurations::value(AvailableSystemConfigurations::CA_CERTIFICATE);

        $privateKey = $this->generatePrivateKey();

        $opensslConfigurationFilePath = $this->createOpenSSLSANConfigFile($dn, $configurationArray);

        $csr = openssl_csr_new($dn, $privateKey, $configurationArray);

        $x509 = openssl_csr_sign($csr, $CACert, $CAPrivateKey, $this->option("days"), $configurationArray, $serialNumber);

        openssl_x509_export($x509, $exportedX509);

        // Important: save certificate to database first
        IssuedCertificate::query()->create([
            "name" => $this->argument("name"),
            "description" => $this->argument("description"),
            "certificate" => $exportedX509,
            "serial_number" => $serialNumber,
            "status" => IssuedCertificate::STATUS_NORMAL,
        ]);

        $this->exportPrivateKey($privateKey);
        $this->saveX509Certificate($exportedX509);

        if ($pkcs12FilePath = $this->option("save-pkcs12-to")) {
            $this->renameOnFileExists($pkcs12FilePath);
            $password = "";
            if ($this->option("pkcs12-password"))
                $password = $this->option("pkcs12-password");

            $this->info(sprintf("Export pkcs12 to %s", $pkcs12FilePath));
            openssl_pkcs12_export($x509, $pkcs12, $privateKey, $password);
            file_put_contents($pkcs12FilePath, $pkcs12);
        }

        $saveFullChain2 = $this->option("save-full-chain-cert-to");
        if (!empty($saveFullChain2)) {
            $this->renameOnFileExists($saveFullChain2);

            $this->info(sprintf("Export x509 full chain certificate to %s", $saveFullChain2));

            $fp = fopen($saveFullChain2, "w+");
            if ($fp) {
                try {
                    fwrite($fp, $exportedX509);
                    fwrite($fp, $CACert);
                    fflush($fp);
                } finally {
                    fclose($fp);
                }
            }
        }

        unlink($opensslConfigurationFilePath);

        $this->call("libvirt:write-conf");
        $this->warn("Certificate issued, save and protect your private key cautiously, BTW: restart libvirtd is required.");

        return 0;
    }
}
