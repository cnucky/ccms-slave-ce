<?php

namespace App\Console\Commands\Storage\Pool;

use App\Constants\Storage;
use App\Utils\Libvirt\LibvirtConnection;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use YunInternet\Libvirt\Configuration\StoragePool;
use YunInternet\Libvirt\Exception\ErrorCode;
use YunInternet\Libvirt\Exception\LibvirtException;

class CreateDefault extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pool:default';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create default storage pool.';

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
        $connection = LibvirtConnection::getConnection();

        if (!is_dir(Storage::DEFAULT_STORAGE_POOL_TARGET))
            mkdir(Storage::DEFAULT_STORAGE_POOL_TARGET, 0700, true);

        try {
            $storagePool = $connection->storagePoolLookupByName(Storage::DEFAULT_STORAGE_POOL_NAME);
        } catch (LibvirtException $e) {
            if ($e->getCode() === ErrorCode::STORAGE_POOL_NOT_FOUND) {
                $storagePoolXML = $this->buildStoragePoolXML();
                $storagePool = $connection->storagePoolDefineXML($storagePoolXML);
            }
            $this->info("Storage pool created.");
        }

        $storagePool->libvirt_storagepool_set_autostart(true);

        try {
            @$storagePool->libvirt_storagepool_create();
        } catch (LibvirtException $e) {
            if ($e->getCode() !== ErrorCode::STORAGE_POOL_IS_ACTIVE)
                throw $e;
        }

        return 0;
    }

    private function buildStoragePoolXML()
    {
        $storagePoolXMLBuilder = new StoragePool(Storage::DEFAULT_STORAGE_POOL_TYPE, Storage::DEFAULT_STORAGE_POOL_NAME);

        /*
        switch (Storage::DEFAULT_STORAGE_POOL_TARGET) {
        }
        */

        $storagePoolXMLBuilder->target()->setPath(Storage::DEFAULT_STORAGE_POOL_TARGET);
        return $storagePoolXMLBuilder->getXML();
    }
}
