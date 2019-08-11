<?php

namespace App\Console\Commands\Storage\ConfigurationLoopback;

use App\Constants\Storage;
use Illuminate\Console\Command;

class Create extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:configuration-loopback:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create configuration loop back file';

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
        if (!file_exists(Storage::DEFAULT_CONFIGURATION_LOOPBACK_FILE_PATH))
            shell_exec("qemu-img create -f raw ". escapeshellarg(Storage::DEFAULT_CONFIGURATION_LOOPBACK_FILE_PATH) ." 4G");
        chmod(Storage::DEFAULT_CONFIGURATION_LOOPBACK_FILE_PATH, 0600);
        $this->info(Storage::DEFAULT_CONFIGURATION_LOOPBACK_FILE_PATH);
        return 0;
    }
}
