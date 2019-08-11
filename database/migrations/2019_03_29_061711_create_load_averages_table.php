<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoadAveragesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('load_averages', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->float('one_minute_average');
            $table->float('five_minutes_average');
            $table->float('fifteen_minutes_average');

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
        Schema::dropIfExists('load_averages');
    }
}
