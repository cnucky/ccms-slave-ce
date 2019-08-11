<?php

namespace App\Console\Commands\System;

use Illuminate\Console\Command;

class DropCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:drop-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop system cache';

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
        system("sync");
        file_put_contents("/proc/sys/vm/drop_caches", "1");
        return 0;
    }
}
