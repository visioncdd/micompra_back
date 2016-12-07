<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrearTablaCategorias extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titulo');
            $table->string('name');
            $table->string('icono')->default('fa fa-tag');
            $table->timestamps();
        });

        Schema::create('subcategorias', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titulo');
            $table->string('name');
            $table->integer('categoria_id');
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
        Schema::drop('categorias');

        Schema::drop('subcategorias');
    }
}
