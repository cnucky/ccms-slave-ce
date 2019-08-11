<?php

namespace App\Console\Commands\Floppy\PublicFloppy;

use App\Utils\Floppy\PublicFloppy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Scan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pvfd:scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan public vfds';

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
        foreach(($publicFloppies = PublicFloppy::scan()) as $name) {
            $values[] = [
                "name" => $name,
            ];
        }
        DB::transaction(function () use (&$values) {
            \App\PublicFloppy::query()->delete();
            \App\PublicFloppy::query()->insert($values);
        });

        print_r($publicFloppies);
        return 0;
    }
}
