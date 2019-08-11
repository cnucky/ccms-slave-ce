<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComputeInstanceCpuStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compute_instance_cpu_statistics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('instance_id');
            $table->double('cpu_time');
            $table->double('user_time');
            $table->double('system_time');
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
        Schema::dropIfExists('compute_instance_cpu_statistics');
    }
}
