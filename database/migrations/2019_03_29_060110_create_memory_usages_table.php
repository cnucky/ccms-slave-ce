<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemoryUsagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('memory_usages', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('total');
            $table->unsignedInteger('free');
            $table->unsignedInteger('available');

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
        Schema::dropIfExists('memory_usages');
    }
}
