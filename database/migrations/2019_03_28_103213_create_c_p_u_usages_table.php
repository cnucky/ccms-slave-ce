<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCPUUsagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cpu_usages', function (Blueprint $table) {
            $table->unsignedBigInteger('basic_cpu_statistics_id');
            $table->char('processor', 8);

            $table->double('user');
            $table->double('nice');
            $table->double('system');
            $table->double('idle');
            $table->double('iowait');
            $table->double('irq');
            $table->double('softirq');
            $table->double('steal');
            $table->double('guest');
            $table->double('guest_nice');

            $table->tinyInteger('uploaded')->default(0);
            $table->index('uploaded');

            $table->primary(["basic_cpu_statistics_id", "processor"]);

            $table->foreign('basic_cpu_statistics_id')
                ->references('id')
                ->on('cpu_statistics')
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
        Schema::dropIfExists('cpu_usages');
    }
}
