<?php

namespace App\Console\Commands\ComputeInstance;

use App\Utils\ComputeInstanceUtils;
use Illuminate\Console\Command;

class Delete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ci:delete {idOrUniqueId} {--delete-attached-disks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete compute instance';

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
        $builder = \App\ComputeInstance::query();
        $idOrUniqueId = $this->argument("idOrUniqueId");
        if (is_numeric($idOrUniqueId))
            $builder->where("id", $idOrUniqueId);
        else
            $builder->where("unique_id", $idOrUniqueId);

        $computeInstance = $builder->firstOrFail();

        $util = new ComputeInstanceUtils($computeInstance->unique_id);
        $util->delete($this->option("delete-attached-disks"));

        $this->info("Domain deleted.");

        return 0;
    }
}
