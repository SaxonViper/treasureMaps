<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTreasureFieldMapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('treasure_field_maps', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('width');
            $table->tinyInteger('height');
            $table->string('title', 50);
            $table->json('cell_barriers');
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
        Schema::dropIfExists('treasure_field_maps');
    }
}
