<?php

namespace App\Console\Commands\ServiceConfiguration\Rsync\Secret;

use App\Constants\AvailableSystemConfigurations;
use App\SystemConfigurations;
use Illuminate\Console\Command;

class SetPublicImageSecret extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rsync:set-public-image-secret {--no-update-secret-file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set rsync public image secret.';

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
        $secret = $this->secret("Secret: ");
        SystemConfigurations::setValue(AvailableSystemConfigurations::PUBLIC_IMAGE_SECRET, $secret);
        $this->info("Secret saved.");
        if (!$this->option("no-update-secret-file"))
            $this->call("rsync:write-secrets");
        return 0;
    }
}
