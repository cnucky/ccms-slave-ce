<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNetworkUsagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('network_usages', function (Blueprint $table) {
            $table->unsignedBigInteger('basic_network_statistics_id');
            $table->string('network_device');

            $table->double('rx_bytes_per_second');
            $table->double('rx_packets_per_second');
            $table->double('tx_bytes_per_second');
            $table->double('tx_packets_per_second');

            $table->tinyInteger('uploaded')->default(0);
            $table->index('uploaded');

            $table->primary(["basic_network_statistics_id", "network_device"]);

            $table->foreign('basic_network_statistics_id')
                ->references('id')
                ->on('network_statistics')
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
        Schema::dropIfExists('network_usages');
    }
}
