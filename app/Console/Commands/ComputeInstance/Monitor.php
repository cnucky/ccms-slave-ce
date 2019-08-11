<?php

namespace App\Console\Commands\ComputeInstance;

use App\Utils\LockFactory;
use Illuminate\Console\Command;

class Monitor extends Command
{
    const LOCK_NAME = "ci:monitor";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ci:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor compute instance status';

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
            $this->call("ci:record-cpu-stat");
            $this->call("ci:record-disk-stat");
            $this->call("ci:record-traffic-usage");

            $this->info("Sleep 15s");
            sleep(15);

            $this->call("ci:record-cpu-stat", ["--calc-usage" => true]);
            $this->call("ci:record-disk-stat", ["--calc-usage" => true]);
            $this->call("ci:record-traffic-usage", ["--calc-bandwidth-usage" => true]);
        } finally {
            $lock
                ->unlock()
                ->clean()
            ;
        }
        return 0;
    }
}
