<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('location')->nullable();
            $table->year('year')->default(date('Y'));
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
            // $table->fullText(['name', 'description', 'location', 'year', 'start_date', 'end_date']);
            $table->fullText('name');
            $table->fullText('description');
            $table->fullText('location');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
