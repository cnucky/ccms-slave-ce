<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSlaveAPIRequestLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slave_a_p_i_request_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string("client_ip");
            $table->string("client_certificate_serial_number");
            $table->string("url")->nullable();
            $table->mediumText("raw_request_body")->nullable();
            $table->mediumText("log")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slave_a_p_i_request_logs');
    }
}
