<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComputeInstanceTrafficUsagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compute_instance_traffic_usages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('network_interface_id');
            // $table->unsignedTinyInteger('direction');
            $table->bigInteger('rx_byte_count')->default(0);
            $table->bigInteger('rx_packet_count')->default(0);
            $table->bigInteger('tx_byte_count')->default(0);
            $table->bigInteger('tx_packet_count')->default(0);
            $table->double('microtime');

            $table->tinyInteger('uploaded')->default(0);
            $table->index('uploaded');

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
        Schema::dropIfExists('compute_instance_traffic_usages');
    }
}
