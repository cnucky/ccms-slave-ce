<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComputeInstanceBandwidthUsagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compute_instance_bandwidth_usages', function (Blueprint $table) {
            $table->unsignedBigInteger('basic_traffic_usage_id');

            $table->unsignedInteger('rx_bytes_per_second');
            $table->unsignedInteger('rx_packets_per_second');
            $table->unsignedInteger('tx_bytes_per_second');
            $table->unsignedInteger('tx_packets_per_second');

            $table->tinyInteger('uploaded')->default(0);
            $table->index('uploaded');

            $table->primary("basic_traffic_usage_id");

            $table->foreign('basic_traffic_usage_id')
                ->references('id')
                ->on('compute_instance_traffic_usages')
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
        Schema::dropIfExists('compute_instance_bandwidth_usages');
    }
}
