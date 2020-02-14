<?php

namespace App\Http\Controllers;

use App\Repositories\FaturamentoRepository;

use Illuminate\Http\Request;
use stdClass;


class FaturamentoController extends Controller
{
    public function __construct(stdClass $rps)
    {
      $this->repository = new FaturamentoRepository();
    }


    public function index()
    {
        //$notas = $this->repository->buscaNotas();   
        return view('faturamento.index');//, compact('notas'));

    }

    public function preConsultarFaturamento($tipo)
    {

    	if ($tipo == 'P')
    		$tipoDesc = 'Período';
    	else
    		$tipoDesc = 'Cliente';

    	$clientes = $this->repository->consultarClientesCompleto();
    	$estados = $this->repository->consultarEstados();
    	$municipios = $this->repository->consultarMunicipios();


        return view('faturamento.pre-consultarfat
            ', compact('tipo', 'tipoDesc', 'clientes', 'estados', 'municipios'));
    }


    public function posConsultarFaturamento($tipo, Request $request)
    {

        $dtIni = $request->dtIni;
        $dtFim = $request->dtFim;

        //periodo
        if($tipo == 'P')
            $lista = $this->repository->consultarFatPeriodo($request->dtIni, 
                $request->dtFim, $request->cmbCliente, $request->cmbEstado, $request->cmbMunicipio);
        else
            //cliente
            $lista = '';
        
        return view('faturamento.pos-consultarfat', compact('lista','dtIni', 'dtFim'));
    }
}
