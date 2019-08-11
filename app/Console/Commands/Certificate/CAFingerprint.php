<?php

namespace App\Console\Commands\Certificate;

use App\Constants\AvailableSystemConfigurations;
use App\SystemConfigurations;
use Illuminate\Console\Command;

class CAFingerprint extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cert:ca-fp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CA Certificate fingerprint';

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

        $fingerprint = openssl_x509_fingerprint($CACertificate);

        $this->info($fingerprint);

        return 0;
    }
}
