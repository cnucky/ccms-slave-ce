<?php

namespace App\Console\Commands\ServiceConfiguration\Cron;

use Illuminate\Console\Command;

class SetCronJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:set-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set cron jobs';

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
        file_put_contents("/etc/cron.d/ccms-slave", <<<EOF
*/2 * * * * root /usr/bin/ccms-slave system:monitor
*/2 * * * * root /usr/bin/ccms-slave ci:monitor
*/5 * * * * root /usr/bin/ccms-slave master:ping

EOF
);
        $this->info("Successfully.");
        return 0;
    }
}
