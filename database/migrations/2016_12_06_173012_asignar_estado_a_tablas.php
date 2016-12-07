<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AsignarEstadoATablas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->integer('estado_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->integer('estado_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('estado_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('estado_id');
        });
    }
}
