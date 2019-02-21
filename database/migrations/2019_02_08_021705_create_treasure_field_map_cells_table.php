<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTreasureFieldMapCellsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('treasure_field_map_cells', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('map_id')->unsigned();
            $table->tinyInteger('posx');
            $table->tinyInteger('posy');
            $table->boolean('is_existing')->default(true);
            $table->string('type', 30)->default('none')->nullable();
            $table->smallInteger('object_param')->nullable();
            $table->unique(['map_id', 'posx', 'posy']);
            $table->foreign('map_id')
                ->references('id')
                ->on('treasure_field_maps')
                ->onDelete('CASCADE');

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
        Schema::dropIfExists('treasure_field_map_cells');
    }
}
