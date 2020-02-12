<?php

namespace App\Http\Controllers;

use App\Repositories\FaturamentoRepository;

use Illuminate\Http\Request;

class FaturamentoController extends Controller
{
    public function __construct(stdClass $rps)
    {
      $this->repository = new FaturamentoRepository();
    }


    public function preConsultarFaturamento()
    {
        return view('notas.pre-consultarfatperiodo
            ');
    }


    public function posConsultarFaturameto(Request $request)
    {
        //periodo
        if($request->tipo == 'P')
            $lista = $this->repository->consultarFatPeriodo($request->dtIni, 
                $request->dtFim, $request->cliente, $request->estado, $request->municipio);
        else
            //cliente
            $lista = '';


        return view('notas.pos-consultarfatperiodo', compact('lista'));
    }
}
