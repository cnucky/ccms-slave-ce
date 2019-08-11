<?php

namespace App\Console\Commands\ISO\PublicISO;

use App\Utils\ISO\PublicISO;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Scan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'piso:scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan public ISOs.';

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
        $values = [];
        foreach(($publicISOs = PublicISO::scan()) as $name) {
            $values[] = [
                "name" => $name,
            ];
        }
        DB::transaction(function () use (&$values) {
            \App\PublicISO::query()->delete();
            \App\PublicISO::query()->insert($values);
        });

        print_r($publicISOs);
        return 0;
    }
}
