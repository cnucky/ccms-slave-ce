<?php

namespace App\Console\Commands\Certificate;

use App\Constants\AvailableSystemConfigurations;
use App\SystemConfigurations;
use App\Utils\Certificates\Certificates;
use Illuminate\Console\Command;

class ExportServerCertificate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cert:export-server';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export server certificate.';

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
        $CACertificate = SystemConfigurations::value(AvailableSystemConfigurations::CA_CERTIFICATE);

        $exportedPrivateKey = SystemConfigurations::value(AvailableSystemConfigurations::SERVER_PRIVATE_KEY);
        Certificates::writeServerPrivateKey($exportedPrivateKey);

        $exportedX509 = SystemConfigurations::value(AvailableSystemConfigurations::SERVER_CERTIFICATE);
        Certificates::writeServerCertificateFile($exportedX509);

        Certificates::writeFullChainServerCertificateFile($exportedX509, $CACertificate);

        $this->info("Server certificate updated.");

        return 0;
    }
}
