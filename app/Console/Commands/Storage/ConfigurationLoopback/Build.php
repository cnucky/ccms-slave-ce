<?php

namespace App\Console\Commands\Storage\ConfigurationLoopback;

use App\Constants\Storage;
use App\Utils\Libvirt\LibvirtConnection;
use Illuminate\Console\Command;

class Build extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:configuration-loopback:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build configuration volume group';

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
        LibvirtConnection::getConnection()->storagePoolLookupByName(Storage::DEFAULT_CONFIGURATION_STORAGE_POOL_NAME)->libvirt_storagepool_build();
        $this->info("Volume group built successfully.");
        return 0;
    }
}
