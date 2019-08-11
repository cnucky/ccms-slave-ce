<?php

namespace App\Console\Commands\ServiceConfiguration\PHP;

use App\Utils\ServiceConfiguration\PHP\PHPFPMInitScriptDefault;
use Illuminate\Console\Command;

class WritePHPFPMInitScriptDefault extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'php-fpm:write-isd';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Write PHP-FPM init script default';

    private $PHPFPMInitScriptDefault;

    /**
     * Create a new command instance.
     *
     * @var PHPFPMInitScriptDefault $PHPFPMInitScriptDefault
     * @return void
     */
    public function __construct(PHPFPMInitScriptDefault $PHPFPMInitScriptDefault)
    {
        parent::__construct();

        $this->PHPFPMInitScriptDefault = $PHPFPMInitScriptDefault;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // $this->PHPFPMInitScriptDefault->makeThenWrite();

        $this->info("PHP-FPM init script default updated.");

        return 0;
    }
}
