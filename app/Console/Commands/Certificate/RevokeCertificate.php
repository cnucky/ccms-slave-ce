<?php

namespace App\Console\Commands\Certificate;

use App\IssuedCertificate;
use Illuminate\Console\Command;

class RevokeCertificate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cert:revoke {cert : Certificate id or certificate name} {--no-update-crl}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revoke a certificate by id';

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
        $cert = $this->argument("cert");
        if (is_numeric($cert)) {
            $certificate = IssuedCertificate::query()->findOrFail($this->argument("cert"));
        } else {
            $certificate = IssuedCertificate::query()->where("name", "=", $cert)->firstOrFail();
        }

        if ($certificate->status == IssuedCertificate::STATUS_REVOKED) {
            $this->error("Certificate #" . $certificate->id . " ". $certificate->name ." already revoked.");
            return 1;
        }

        $certificate->update([
            "revoke_time" => date("Y-m-d H:i:s"),
            "status" => IssuedCertificate::STATUS_REVOKED,
        ]);

        $this->info("Certificate #" . $certificate->id . " ". $certificate->name ." revoked.");

        if ($this->option("no-update-crl")) {
            $this->warn("Remember to use command cert:generate-crl to update crl.");
        } else {
            $this->call("cert:generate-crl");
        }
        return 0;
    }
}
