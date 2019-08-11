<?php

namespace App\Console\Commands\Storage\ConfigurationLoopback;

use App\Constants\Storage;
use App\Utils\Libvirt\LibvirtConnection;
use Illuminate\Console\Command;
use YunInternet\Libvirt\Configuration\StoragePool;
use YunInternet\Libvirt\Exception\ErrorCode;
use YunInternet\Libvirt\Exception\LibvirtException;

class Config extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:configuration-loopback:config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Init configuration loopback';

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
     * @throws LibvirtException
     */
    public function handle()
    {
        $storagePoolConfiguration = new StoragePool("logical", Storage::DEFAULT_CONFIGURATION_STORAGE_POOL_NAME);
        $storagePoolConfiguration->source()->addDevice(Storage::configurationLoopbackDevice());
        $storagePoolConfiguration->target()->setPath("/dev/" . Storage::DEFAULT_CONFIGURATION_STORAGE_POOL_NAME);

        $uuid = $this->getUUID();
        if ($uuid)
            $storagePoolConfiguration->addChild("uuid", $uuid);

        LibvirtConnection::getConnection()->storagePoolDefineXML($storagePoolConfiguration->getXML());
        $this->info("Storage poll " . Storage::DEFAULT_CONFIGURATION_STORAGE_POOL_NAME . " defined.");
        return 0;
    }

    private function getUUID()
    {
        $uuid = null;
        try {
            $uuid = LibvirtConnection::getConnection()->storagePoolLookupByName(Storage::DEFAULT_CONFIGURATION_STORAGE_POOL_NAME)->libvirt_storagepool_get_uuid_string();
        } catch (LibvirtException $libvirtException) {
            if ($libvirtException->getCode() !== ErrorCode::STORAGE_POOL_NOT_FOUND)
                throw $libvirtException;
        }
        return $uuid;
    }
}
