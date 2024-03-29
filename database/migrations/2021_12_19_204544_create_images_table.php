<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->foreign('event_id')->references('id')->on('events');
            $table->string('path')->nullable();
            $table->string('name')->nullable();
            $table->string('extension')->nullable();
            $table->string('alt')->nullable();
            $table->string('title')->nullable();
            $table->softDeletes();
            $table->timestamps();
            // $table->fullText(['name', 'alt', 'title']);
            $table->fullText('name');
            $table->fullText('alt');
            $table->fullText('title');
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
