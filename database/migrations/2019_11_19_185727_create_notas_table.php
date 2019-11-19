<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('st_central.notas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('inscricaomunicipalprestador');
            $table->string('razaosocialprestador');
            
            $table->string('tiporps', 5);
            $table->string('serierps', 20);
            $table->bigInteger('numerorps');
            $table->dateTime('dataemissaorps');
            $table->char('situacaorps', 2);
            $table->string('serieprestacao', 20);
            
            $table->string('inscricaomunicipaltomador');
            $table->string('cpfcnpjtomador');
            $table->string('razaosocialtomador');
            $table->string('tipologradourotomador');
            $table->string('logradourotomador');
            $table->string('numeroenderecotomador');
            $table->string('complementoenderecotomador');
            $table->string('tipobairrotomador');
            $table->string('bairrotomador');
            $table->string('cidadetomador');
            $table->string('cidadetomadordescricao');
            $table->string('ceptomador');
            $table->string('emailtomador');
            
            $table->string('codigoatividade');
            $table->float('aliquotaatividade', 14, 2);
            $table->string('tiporecolhimento');
            $table->string('municipioprestacao');
            $table->string('municipioprestacaodescricao');
            $table->char('operacao', 2);
            $table->char('tributacao', 2);
            $table->float('valorpis', 14, 2);
            $table->float('valorcofins', 14, 2);
            $table->float('valorinss', 14, 2);
            $table->float('valorir', 14, 2);
            $table->float('valorcsll', 14, 2);
            $table->float('aliquotapis', 14, 2);
            $table->float('aliquotacofins', 14, 2);
            $table->float('aliquotainss', 14, 2);
            $table->float('aliquotair', 14, 2);
            $table->float('aliquotacsll', 14, 2);
            $table->text('descricaorps');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notas1');
    }
}
