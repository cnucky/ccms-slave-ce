<?php

namespace App\Console\Commands\System;

use App\MemoryUsage;
use App\Utils\LockFactory;
use App\Utils\System\Memory;
use Illuminate\Console\Command;

class RecordMemoryStat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:mem:record-stat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record system memory stat';

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
        $meminfo = Memory::memoryInformation();
        MemoryUsage::query()->create([
            "total" => $meminfo["total"],
            "free" => $meminfo["free"],
            "available" => $meminfo["available"],
            "microtime" => $now = microtime(true),
        ]);

        $this->info("System memory statistics recorded at " . $now . ".");
        return 0;
    }
}
