<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItensnotaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('st_central.itens_nota', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->BigInteger('id_nota');

            $table->foreign('id_nota')->references('id')->on('st_central.notas');


            $table->timestamps();
            $table->text('discriminacaoservico');
            $table->integer('quantidade');
            $table->float('valorunitario', 14, 2);
            $table->float('valortotal', 14, 2);
            $table->char('tributavel', 2);


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('itens_nota');
    }
}
