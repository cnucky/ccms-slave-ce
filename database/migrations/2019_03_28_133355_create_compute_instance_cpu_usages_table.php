<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComputeInstanceCpuUsagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compute_instance_cpu_usages', function (Blueprint $table) {
            $table->unsignedBigInteger('basic_cpu_statistics_id');

            $table->double('cpu_usage');
            $table->double('user_usage');
            $table->double('system_usage');

            $table->tinyInteger('uploaded')->default(0);
            $table->index('uploaded');

            $table->primary("basic_cpu_statistics_id");

            $table->foreign('basic_cpu_statistics_id')
                ->references('id')
                ->on('compute_instance_cpu_statistics')
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
        Schema::dropIfExists('compute_instance_cpu_usages');
    }
}
