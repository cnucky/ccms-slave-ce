<?php

namespace App\Console\Commands\ServiceConfiguration\Libvirt;

use App\Constants\AvailableSystemConfigurations;
use App\SystemConfigurations;
use Illuminate\Console\Command;

class InitialConfiguration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'libvirt:initial-conf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up initial configuration for libvirtd';

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
        SystemConfigurations::setValueIfNotExists(AvailableSystemConfigurations::LIBVIRTD_LISTEN_TLS, "1");
        SystemConfigurations::setValueIfNotExists(AvailableSystemConfigurations::LIBVIRTD_LISTEN_TCP, "0");
        SystemConfigurations::setValueIfNotExists(AvailableSystemConfigurations::LIBVIRTD_TLS_PORT, "16514");
        SystemConfigurations::setValueIfNotExists(AvailableSystemConfigurations::LIBVIRTD_TCP_PORT, "16509");
        SystemConfigurations::setValueIfNotExists(AvailableSystemConfigurations::LIBVIRTD_LISTEN_ADDRESS, "0.0.0.0");
        SystemConfigurations::setValueIfNotExists(AvailableSystemConfigurations::LIBVIRTD_LOG_LEVEL, "3");
        SystemConfigurations::setValueIfNotExists(AvailableSystemConfigurations::LIBVIRTD_LOG_OUTPUTS, "3:syslog:libvirtd");
        SystemConfigurations::setValueIfNotExists(AvailableSystemConfigurations::LIBVIRTD_ADDITIONAL_CONFIGURATION, "");

        $this->info("libvirtd initial configuration saved.");
        return 0;
    }
}
