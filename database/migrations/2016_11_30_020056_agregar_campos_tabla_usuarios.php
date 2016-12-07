<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgregarCamposTablaUsuarios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tel')->nullable();
            $table->integer('estado_id')->nullable();
            $table->string('foto')->nullable();
            $table->string('name')->nullable()->change();
            $table->boolean('facebook')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tel');
            $table->dropColumn('facebook');
            $table->dropColumn('estado');
            $table->dropColumn('foto');
            $table->string('name')->change();
        });
    }
}
