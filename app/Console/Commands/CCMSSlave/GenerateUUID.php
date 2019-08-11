<?php

namespace App\Console\Commands\CCMSSlave;

use App\Constants\AvailableSystemConfigurations;
use App\SystemConfigurations;
use Illuminate\Console\Command;
use Ramsey\Uuid\Uuid;

class GenerateUUID extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccms:slave:generate-uuid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate uuid for ccms slave';

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
        if (!empty($currentUUID = SystemConfigurations::value(AvailableSystemConfigurations::CCMS_SLAVE_UUID))) {
            if (!$this->confirm("UUID:$currentUUID is using by this node now, do you want to overwrite it?"))
                return 0;
        }

        exec("/sbin/ip route show default", $output, $return_var);
        if ($return_var !== 0)
            return $return_var;
        if (!count($output))
            $this->error("Default route not found.");

        $defaultRouteRecord = explode(" ", $output[0]);
        $isDeviceName = false;
        $defaultRouteDevice = false;
        foreach ($defaultRouteRecord as $value) {
            if ($isDeviceName) {
                $defaultRouteDevice = $value;
                break;
            } else if ($value === "dev") {
                $isDeviceName = true;
            }
        }
        if ($defaultRouteDevice === false)
            $this->error("Default route device not found.");
        $rawMacAddress = trim(file_get_contents("/sys/class/net/$defaultRouteDevice/address"));
        $this->info("Use nic $defaultRouteDevice's mac address: $rawMacAddress.");
        $node = str_replace([':', '-'], '', $rawMacAddress);

        $uuid = Uuid::uuid1($node)->__toString();
        SystemConfigurations::setValue(AvailableSystemConfigurations::CCMS_SLAVE_UUID, $uuid);
        $this->info($uuid);
        return 0;
    }
}
