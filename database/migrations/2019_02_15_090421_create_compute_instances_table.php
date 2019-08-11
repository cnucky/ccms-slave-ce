<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComputeInstancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compute_instances', function (Blueprint $table) {
            $table->increments('id');
            $table->string('unique_id')->unique();

            $table->char('host_only_nic_mac_address', 17)->unique();

            $table->tinyInteger('first_boot')->default(1);

            $table->string('os', 16)->nullable();
            // $table->tinyInteger('need_password_reset')->default(1);
            // $table->tinyInteger('need_network_reset')->default(1);

            // $table->text('configuration');

            $table->tinyInteger('status')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('compute_instances');
    }
}
