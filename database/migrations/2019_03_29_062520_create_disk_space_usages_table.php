<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiskSpaceUsagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disk_space_usages', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->double('total');
            $table->double('free');

            $table->tinyInteger('uploaded')->default(0);
            $table->index('uploaded');

            $table->double('microtime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('disk_space_usages');
    }
}
