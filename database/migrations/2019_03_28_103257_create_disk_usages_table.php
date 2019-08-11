<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiskUsagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disk_usages', function (Blueprint $table) {
            $table->unsignedBigInteger('basic_disk_statistics_id');
            $table->string('block_device');

            $table->double('read_bytes_per_second');
            $table->double('write_bytes_per_second');

            $table->tinyInteger('uploaded')->default(0);
            $table->index('uploaded');

            $table->primary(["basic_disk_statistics_id", "block_device"]);

            $table->foreign('basic_disk_statistics_id')
                ->references('id')
                ->on('disk_statistics')
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
        Schema::dropIfExists('disk_usages');
    }
}
