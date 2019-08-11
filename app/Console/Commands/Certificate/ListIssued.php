<?php

namespace App\Console\Commands\Certificate;

use App\IssuedCertificate;
use Illuminate\Console\Command;

class ListIssued extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cert:list-issued {--id= : Search by id} {--name= : Search by name} {--all : Show revoked certificates}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List certificates issued by current CA';

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
        $builder = IssuedCertificate::query();

        if (!$this->option("all")) {
            $builder->where("status", "=", IssuedCertificate::STATUS_NORMAL);
        }

        if ($id = $this->option("id")) {
            $builder->where("id", "=", $id);
        }

        if ($name = $this->option("name")) {
            $builder->where("name", "LIKE", $name . "%");
        }

        $this->table(["ID", "Name", "Description", "Serial number", "Status", "Created at", "Revoked at"], $builder->get(["id", "name", "description", "serial_number", "status", "created_at", "revoke_time"]));

        return 0;
    }
}
