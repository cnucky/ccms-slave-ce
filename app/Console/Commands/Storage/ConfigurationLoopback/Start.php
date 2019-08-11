<?php

namespace App\Console\Commands\Storage\ConfigurationLoopback;

use App\Constants\Storage;
use App\Utils\Libvirt\LibvirtConnection;
use Illuminate\Console\Command;

class Start extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:configuration-loopback:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start configuration pool';

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
        LibvirtConnection::getConnection()->storagePoolLookupByName(Storage::DEFAULT_CONFIGURATION_STORAGE_POOL_NAME)->libvirt_storagepool_create();
        $this->info("Started successfully.");
    }
}
