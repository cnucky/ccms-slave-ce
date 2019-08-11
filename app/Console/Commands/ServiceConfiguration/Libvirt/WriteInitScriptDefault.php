<?php

namespace App\Console\Commands\ServiceConfiguration\Libvirt;

use App\Utils\ServiceConfiguration\Libvirt\LibvirtdInitScriptDefault;
use Illuminate\Console\Command;

class WriteInitScriptDefault extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'libvirt:write-isd';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Write libvirtd init script default';

    private $libvirtdInitScriptDefault;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(LibvirtdInitScriptDefault $libvirtdInitScriptDefault)
    {
        parent::__construct();

        $this->libvirtdInitScriptDefault = $libvirtdInitScriptDefault;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->libvirtdInitScriptDefault
            ->startLibvirtd(true)
            ->libvirtdOptions("-l")
            ->makeThenWrite()
        ;

        $this->info("libvirtd init script default updated.");

        return 0;
    }
}
