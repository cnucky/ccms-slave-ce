<?php

namespace App\Console\Commands\Storage\ConfigurationLoopback;

use App\Constants\Storage;
use Illuminate\Console\Command;

class Associate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:configuration-loopback:associate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Associate configuration loopback file to loopback device';

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
        shell_exec(sprintf("losetup -v %s %s", escapeshellarg(Storage::configurationLoopbackDevice()), escapeshellarg(Storage::DEFAULT_CONFIGURATION_LOOPBACK_FILE_PATH)));
        shell_exec("losetup --all");
        return 0;
    }
}
