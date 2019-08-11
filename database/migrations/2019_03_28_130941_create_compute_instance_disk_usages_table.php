<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComputeInstanceDiskUsagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compute_instance_disk_usages', function (Blueprint $table) {
            $table->unsignedBigInteger('basic_disk_statistics_id');

            $table->double('rd_req_per_second');
            $table->double('rd_bytes_per_second');
            $table->double('wr_req_per_second');
            $table->double('wr_bytes_per_second');

            $table->tinyInteger('uploaded')->default(0);
            $table->index('uploaded');

            $table->primary("basic_disk_statistics_id");

            $table->foreign('basic_disk_statistics_id')
                ->references('id')
                ->on('compute_instance_disk_statistics')
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
        Schema::dropIfExists('compute_instance_disk_usages');
    }
}
