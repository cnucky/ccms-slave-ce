<?php

namespace App\Console\Commands\System;

use App\Utils\LockFactory;
use Illuminate\Console\Command;

class Monitor extends Command
{
    const LOCK_NAME = "system:monitor";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor system status';

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
        $lock = LockFactory::getLocker(self::LOCK_NAME);
        $lock->exclusive(true);

        try {
            $this->call("system:disk-space:record-usage");
            $this->call("system:mem:record-stat");
            $this->call("system:network:record-stat");
            $this->call("system:disk:record-stat");
            $this->call("system:load-avg:record");
            $this->call("system:cpu:record-stat");
            $this->info("Sleep 10s.");
            sleep(10);
            $this->call("system:cpu:record-stat", ["--calc-usage" => true]);
            $this->call("system:disk:record-stat", ["--calc-usage" => true]);
            $this->call("system:network:record-stat", ["--calc-usage" => true]);
            return 0;
        } finally {
            $lock
                ->unlock()
                ->clean()
            ;
        }
    }
}
