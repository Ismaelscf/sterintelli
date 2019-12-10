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
        //Schema::create('st_central.notas', function (Blueprint $table) {
        Schema::create('notas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('cnpj')->nullable();
            $table->string('razao')->nullable();
            $table->date('dtInicial');
            $table->date('dtFinal');
            $table->integer('qtdade');
            $table->float('vTotServ');
            $table->float('vTotDeduc');

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
            $table->string('tipologradourotomador')->nullable();
            $table->string('logradourotomador')->nullable();
            $table->string('numeroenderecotomador')->nullable();
            $table->string('complementoenderecotomador')->nullable();
            $table->string('tipobairrotomador')->nullable();
            $table->string('bairrotomador')->nullable();
            $table->string('cidadetomador')->nullable();
            $table->string('cidadetomadordescricao')->nullable();
            $table->string('ceptomador');
            $table->string('emailtomador');
            
            $table->string('codigoservico');
            $table->string('codigoatividade');
            $table->float('aliquotaatividade', 14, 2);
            $table->string('tiporecolhimento');
            $table->string('municipioprestacao');
            $table->string('municipioprestacaodescricao')->nullable();
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
            $table->text('descricaorps')->nullable();
            $table->dateTime("last_update");
            $table->dateTime("creation_date");
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
