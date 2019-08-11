<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComputeInstanceDiskStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compute_instance_disk_statistics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('instance_id');
            $table->bigInteger('rd_req');
            $table->bigInteger('rd_bytes');
            $table->bigInteger('wr_req');
            $table->bigInteger('wr_bytes');
            $table->double('microtime');

            $table->foreign('instance_id')
                ->references('id')
                ->on('compute_instances')
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
        Schema::dropIfExists('compute_instance_disk_statistics');
    }
}
