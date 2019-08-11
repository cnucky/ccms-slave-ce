<?php

namespace App\Console\Commands\System;

use App\DiskSpaceUsage;
use Illuminate\Console\Command;
use YunInternet\CCMSCommon\Constants\Constants;

class RecordDiskSpaceUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:disk-space:record-usage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record system disk space usage';

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
        $total = disk_total_space(Constants::CCMS_SLAVE_DATA_DIRECTORY);
        $free = disk_free_space(Constants::CCMS_SLAVE_DATA_DIRECTORY);

        DiskSpaceUsage::query()->create([
            "total" => $total,
            "free" => $free,
            "microtime" => $now = microtime(true),
        ]);

        $this->info("Disk space usage recorded at " . $now . ".");
        return 0;
    }
}
