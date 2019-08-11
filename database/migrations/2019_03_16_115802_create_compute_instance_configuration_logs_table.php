<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComputeInstanceConfigurationLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compute_instance_configuration_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('unique_id');
            $table->mediumText('configuration')->nullable();
            $table->mediumText('log')->nullable();
            $table->unsignedTinyInteger('status')->nullable();
            $table->timestamps();

            $table->index("unique_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('compute_instance_configuration_logs');
    }
}
