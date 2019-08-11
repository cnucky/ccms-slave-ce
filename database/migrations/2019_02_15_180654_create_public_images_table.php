<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePublicImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('public_images', function (Blueprint $table) {
            $table->string('name');
            $table->unsignedInteger('version');
            $table->string('path');
            $table->string('format')->nullable();
            $table->tinyInteger('type')->default(\App\Constants\Image\ImageTypeCode::TYPE_AUTO_DISCOVERED);

            $table->primary(["name", "version"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('images');
    }
}
