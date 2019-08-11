<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-16
 * Time: 下午11:58
 */

namespace App\Utils\ComputeInstance;


use App\ComputeInstance;
use App\Constants\ComputeInstance\StatusCode;
use App\Constants\Storage;
use App\PublicImage;
use App\Utils\Libvirt\LibvirtConnection;
use App\Utils\LocalVolume\LocalVolumeFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use YunInternet\Libvirt\Configuration\StorageVolume;

class SetupRequestParser
{
    protected static $__unit32_validate_rule = "nullable|integer|min:0|max:" . 4294967295;

    private $requestData;

    public function __construct($requestData)
    {
        $this->requestData = $requestData;
        $this->validate();
    }

    public function setup()
    {
        $volumes = $this->createVolumes();

        try {
            $computeInstanceUtil = new Configuration2XML($this->requestData);
            return $computeInstanceUtil->define();
        } catch (\Throwable $throwable) {
            // Delete all volumes
            foreach ($volumes as $volume) {
                /**
                 * @var \YunInternet\Libvirt\StorageVolume $volume
                 */
                try {
                    $volume->libvirt_storagevolume_delete();
                } catch (\Throwable $e) {
                }
            }

            throw $throwable;
        }
    }

    public function reconfigure(&$generatedXML = null, $formatted = false)
    {
        $computeInstanceUtil = new Configuration2XML($this->requestData);
        return $computeInstanceUtil->define($generatedXML, $formatted);
    }

    /**
     * @return \YunInternet\Libvirt\StorageVolume[]
     * @throws \Throwable
     */
    private function createVolumes()
    {
        /*
        // Create configuration volume
        $configurationVolumeFactory = new LocalVolumeFactory(Storage::DEFAULT_CONFIGURATION_STORAGE_POOL_NAME);
        $configurationVolume = $configurationVolumeFactory
            ->withCapacity(16)
            ->withFormat(null)
            ->create($this->requestData["unique_id"])
        ;
        */

        $createdVolumes = [];

        $localVolumeFactory = new LocalVolumeFactory();

        $localVolumeFactory
            ->withFormat("qcow2")
        ;

        try {
            foreach (@$this->requestData["volumes"] as $order => $volume) {
                $localVolumeFactory
                    ->withCapacity($volume["capacity"])
                ;

                if (isset($volume["backing_store"])) {
                    $backingStore = $volume["backing_store"];
                    switch ($backingStore["type"]) {
                        case "image":
                            $image = PublicImage::query()->where("name", "=", $backingStore["image"])->orderByDesc("version")->first();
                            if (!$image)
                                throw ValidationException::withMessages(["image" => "image not exists"]);
                            $localVolumeFactory->withBackingStore($image->path, $image->format);
                            break;
                    }
                } else {
                    $localVolumeFactory->withBackingStore(null, null);
                }

                $createdVolumes[] = $localVolumeFactory->create($volume["unique_id"]);
            }
        } catch (\Throwable $e) {
            foreach ($createdVolumes as $createdVolume)
                $createdVolume->libvirt_storagevolume_delete();
            // $configurationVolume->libvirt_storagevolume_delete();
            throw $e;
        }

        return $createdVolumes;
    }

    private function validate()
    {
        $this->validateCommonValues();
        $this->passwordValidate();
        $this->vncPasswordValidate();
    }

    private function validateCommonValues()
    {
        /**
         * @var \Illuminate\Validation\Validator $validator
         */
        $validator = Validator::make($this->requestData, [
            "unique_id" => "required|max:64",
            "hostname" => [
                "nullable",
                "regex:/^[a-zA-Z\-\.\d]{2,64}$/",
            ],
            "vCPU" => "required|integer|min:1",
            "memory" => "required|integer|min:1",
            "inbound_bandwidth" => self::$__unit32_validate_rule,
            "outbound_bandwidth" => self::$__unit32_validate_rule,
            "io_weight" => "nullable|integer|min:10|max:1000",
            "read_bytes_sec" => self::$__unit32_validate_rule,
            "write_bytes_sec" => self::$__unit32_validate_rule,
            "read_iops_sec" => self::$__unit32_validate_rule,
            "write_iops_sec" => self::$__unit32_validate_rule,
        ]);

        $validator->validate();
    }
    
    private function passwordValidate()
    {
        Validator::make($this->requestData, [
            "password" => "nullable|max:255",
        ])->validate();
    }

    private function vncPasswordValidate()
    {
        Validator::make($this->requestData, [
            "vnc_password" => "nullable|max:8",
        ])->validate();
    }

    private function retrieveCommonValues()
    {
        return [
            "unique_id" => @$this->requestData["unique_id"],
            "hostname" => @$this->requestData["hostname"] ? @$this->requestData["hostname"] : @$this->requestData["unique_id"],
            "vCPU" => @$this->requestData["vCPU"],
            "memory" => @$this->requestData["memory"],
            "inbound_bandwidth" => @$this->requestData["inbound_bandwidth"],
            "outbound_bandwidth" => @$this->requestData["outbound_bandwidth"],
            "io_weight" => @$this->requestData["io_weight"],
            "read_bytes_sec" => @$this->requestData["read_bytes_sec"],
            "write_bytes_sec" => @$this->requestData["write_bytes_sec"],
            "read_iops_sec" => @$this->requestData["read_iops_sec"],
            "write_iops_sec" => @$this->requestData["write_iops_sec"],
            "no_clean_traffic" => intval(boolval(@$this->requestData["no_clean_traffic"])),
        ];
    }

    private function retrievePassword(&$values = [])
    {
        $values["password"] = @$this->requestData["password"];
        return @$this->requestData["password"];
    }

    private function retrieveVNCPassword( &$values = [])
    {
        $vncPassword = @$this->requestData["vnc_password"] ? @$this->requestData["vnc_password"] : mt_rand(10000000, 99999999);
        $values["vnc_password"] = $vncPassword;
        return $vncPassword;
    }
}