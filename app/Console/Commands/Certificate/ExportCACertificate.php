<?php

namespace App\Console\Commands\Certificate;

use App\Constants\AvailableSystemConfigurations;
use App\SystemConfigurations;
use App\Utils\Certificates\Certificates;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class ExportCACertificate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cert:export-ca';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export CA certificate.';

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
        $exportedCACertificate = SystemConfigurations::value(AvailableSystemConfigurations::CA_CERTIFICATE);
        Certificates::writeCACertificate($exportedCACertificate);

        $this->info("CA certificate exported.");

        return 0;
    }
}
