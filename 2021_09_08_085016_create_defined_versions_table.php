<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDefinedVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('defined_versions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('defined_act_ru')->nullable();
            $table->integer('defined_act_en')->nullable();
            $table->integer('defined_unact_ru')->nullable();
            $table->integer('defined_unact_en')->nullable();
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
        Schema::dropIfExists('defined_versions');
    }
}
