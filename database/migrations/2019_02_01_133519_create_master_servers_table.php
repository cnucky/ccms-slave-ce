<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMasterServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_servers', function (Blueprint $table) {
            $table->increments('id');

            $table->string('master_id');
            $table->string('host');
            $table->tinyInteger('slave_type');
            $table->string('token')->nullable();
            $table->timestamp('last_communicate_at')->nullable();
            $table->timestamps();

            $table->unique(['master_id', 'slave_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_servers');
    }
}
