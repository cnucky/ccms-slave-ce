<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComputeInstanceNetworkInterfaceIPv4sTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compute_instance_network_interface_ipv4s', function (Blueprint $table) {
            $table->unsignedInteger('network_interface_id');
            $table->string('ip');
            $table->string('gateway')->nullable();
            $table->unsignedTinyInteger('mask');
            $table->unsignedTinyInteger('pool_mask');
            $table->timestamps();

            $table->primary(["network_interface_id", "ip"]);

            $table->foreign('network_interface_id')
                ->references('id')
                ->on('compute_instance_network_interfaces')
                ->onDelete('cascade')
                ->onUpdate('cascade')
            ;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('compute_instance_network_interface_ipv4s');
    }
}
