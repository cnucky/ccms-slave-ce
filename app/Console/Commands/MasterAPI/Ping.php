<?php

namespace App\Console\Commands\MasterAPI;

use App\ComputeInstance;
use App\ComputeInstance\BandwidthUsage;
use App\ComputeInstance\TrafficUsage;
use App\Constants\UploadStatus;
use App\CPUUsage;
use App\DiskSpaceUsage;
use App\DiskUsage;
use App\LoadAverage;
use App\MasterServer;
use App\MemoryUsage;
use App\NetworkUsage;
use App\PublicImage;
use App\Utils\Libvirt\LibvirtConnection;
use App\Utils\LockFactory;
use App\Utils\Master\MasterAPIRequestFactory;
use App\Utils\System\CPU;
use App\Utils\System\Uptime;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use YunInternet\CCMSCommon\Constants\NetworkType;
use YunInternet\CCMSCommon\Constants\SlaveType;
use YunInternet\CCMSCommon\Network\Exception\APIRequestException;

class Ping extends Command
{
    const LOCK_NAME = "master:ping";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'master:ping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send ping to master server(s).';

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
        $locker = LockFactory::getLocker(self::LOCK_NAME);
        $locker->exclusive(true);

        try {
            $masterServer = MasterServer::query()->where("slave_type", SlaveType::COMPUTE_NODE)->firstOrFail();

            $publicImages = PublicImage::query()->get(["name", "version"])->mapToGroups(function ($item, $key) {
                return [$item["name"] => $item["version"]];
            });

            DB::beginTransaction();

            self::updateUsageReady2Uploading();

            $data = [
                "type" => "ping",
                "data" => [
                    "timestamp" => time(),
                    "nodeStatus" => [
                        "uptime" => Uptime::getUptime(),
                        "cpu" => [
                            "information" => CPU::getCPUInformation(),
                            "usage" => CPUUsage::query()
                                ->where(self::whereUploadingClosure())
                                ->with("basicCPUStatistics:id,microtime")
                                ->get(),
                        ],
                        "memory" => [
                            "usage" => MemoryUsage::query()->where(self::whereUploadingClosure())->get(),
                        ],
                        "disk" => [
                            "ioUsage" => DiskUsage::query()
                                ->where(self::whereUploadingClosure())
                                ->with("basicDiskStatistics:id,microtime")
                                ->get()
                            ,
                            "spaceUsage" => DiskSpaceUsage::query()->where(self::whereUploadingClosure())->get(),
                        ],
                        "loadAverage" => LoadAverage::query()->where(self::whereUploadingClosure())->get(),
                        "network" => [
                            "bandwidthUsage" => NetworkUsage::query()
                                ->where(self::whereUploadingClosure())
                                ->with("basicNetworkStatistics:id,microtime")
                                ->get()
                            ,
                        ],
                    ],
                    "images" => ["public" => $publicImages],
                    "isos" => ["public" => \App\PublicISO::query()->pluck("name")],
                    "floppies" => ["public" => \App\PublicFloppy::query()->pluck("name")],
                    "instances" => self::retrieveComputeInstanceStatus(),
                ],
            ];

            print_r(json_decode(json_encode($data), true));

            echo PHP_EOL;
            $this->info("Ping: " . $masterServer->host);

            $APIRequest = MasterAPIRequestFactory::makeDirectly($masterServer->host, $masterServer->id, $masterServer->token, "/ping")->withPostFields($data, true);

            try {
                $response = $APIRequest->JSONResponse($rawResponse);
                $this->line($rawResponse);
                if (@$response->result) {
                    self::cleanUploadingUsageRecords();
                    $this->info("success");
                    DB::commit();
                    // DB::rollback();
                } else {
                    $this->error("fail");
                    DB::rollback();
                }
            } catch (APIRequestException $e) {
                DB::rollback();
                $this->line($rawResponse);
                $this->error("fail: " . $e->getMessage());
            }

            return 0;
        } finally {
            $locker
                ->unlock()
                ->clean()
            ;
        }
    }

    private function updateUsageReady2Uploading()
    {
        // System
        self::updateReady2Uploading(CPUUsage::query());
        self::updateReady2Uploading(DiskUsage::query());
        self::updateReady2Uploading(NetworkUsage::query());
        self::updateReady2Uploading(MemoryUsage::query());
        self::updateReady2Uploading(LoadAverage::query());
        self::updateReady2Uploading(DiskSpaceUsage::query());

        // Compute instance
        self::updateReady2Uploading(\App\ComputeInstance\CPUUsage::query());
        self::updateReady2Uploading(\App\ComputeInstance\DiskUsage::query());
        self::updateReady2Uploading(TrafficUsage::query());
        self::updateReady2Uploading(BandwidthUsage::query());
    }

    private function cleanUploadingUsageRecords()
    {
        // System
        self::cleanUploading(CPUUsage::query());
        self::cleanUploading(DiskUsage::query());
        self::cleanUploading(NetworkUsage::query());
        self::cleanUploading(MemoryUsage::query());
        self::cleanUploading(LoadAverage::query());
        self::cleanUploading(DiskSpaceUsage::query());

        // Compute instance
        self::cleanUploading(\App\ComputeInstance\CPUUsage::query());
        self::cleanUploading(\App\ComputeInstance\DiskUsage::query());
        self::cleanUploading(TrafficUsage::query());
        self::cleanUploading(BandwidthUsage::query());
    }

    private function whereUploadingClosure($tableName = null)
    {
        return function ($builder) use ($tableName) {
            $columnName = "uploaded";
            if (!empty($tableName))
                $columnName = $tableName . "." . $columnName;
            $builder->where($columnName, UploadStatus::STATUS_UPLOADING);
        };
    }

    private function microtimeOnlyClosure($tableName = null)
    {
        return function ($builder) use ($tableName) {
            if (!empty($tableName)) {
                $builder->select([$tableName . ".id", $tableName . ".microtime"]);
            } else {
                $builder->select(["id", "microtime"]);
            }
        };
    }

    private function retrieveComputeInstanceStatus()
    {
        $instanceStatus = [];
        $instanceUniqueIds = LibvirtConnection::getConnection()->libvirt_list_domains();

        /**
         * @var ComputeInstance[] $instanceModels
         */
        $instanceModels = ComputeInstance::query()
            ->whereIn("unique_id", $instanceUniqueIds)
            ->with([
                "cpuUsages" => self::whereUploadingClosure(),
                "cpuUsages.basicCPUStatistics" => self::microtimeOnlyClosure(),
                "diskUsages" => self::whereUploadingClosure(),
                "diskUsages.basicDiskStatistics" => self::microtimeOnlyClosure(),
                "networkInterfaces" => function ($bulder) {
                    $bulder
                        ->whereIn("type", [NetworkType::TYPE_PUBLIC_NETWORK, NetworkType::TYPE_PRIVATE_NETWORK])
                    ;
                },
                "networkInterfaces.trafficUsages" => self::whereUploadingClosure(),
                "networkInterfaces.bandwidthUsages" => self::whereUploadingClosure("compute_instance_bandwidth_usages"),
                "networkInterfaces.bandwidthUsages.basicTrafficUsage" => self::microtimeOnlyClosure(),
            ])
            ->get()
            ->keyBy("unique_id")
        ;

        foreach ($instanceModels as $uniqueId => $instanceModel) {
            try {
                $domain = LibvirtConnection::getConnection()->domainLookupByName($instanceModel->unique_id);
                $powerStatus = $domain->libvirt_domain_is_active();
            } catch (\Exception $e) {
                $powerStatus = 0;
            }

            $instanceStatus[$uniqueId] = [
                "power" => $powerStatus,
                "status" => $instanceModel
            ];
        }

        return $instanceStatus;
    }

    private static function updateReady2Uploading(Builder $builder)
    {
        $builder
            ->where("uploaded", UploadStatus::STATUS_READY_FOR_UPLOADING)
            ->update([
                "uploaded" => UploadStatus::STATUS_UPLOADING,
            ])
        ;
    }

    private static function cleanUploading(Builder $builder)
    {
        $builder->where("uploaded", UploadStatus::STATUS_UPLOADING)->delete();
    }
}
