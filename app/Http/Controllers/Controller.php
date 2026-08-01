<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use NumberFormatter;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $repository;

    //variável usada para repassar mensagens à view
    protected $msgInforma = [];

    public function __construct()
    {
      date_default_timezone_set('America/Bahia');
      setlocale(LC_MONETARY,"pt_BR");
    }

    function formataDataHoraEnvio($data, $hora) {
      $array = explode('/', $data);
      $dataFinal =  $array[2].'-'.$array[1].'-'.$array[0].'T'.$hora;
      return $dataFinal;
    }

    function formataDataEnvio($data) {
      $array = explode('/', $data);
      $dataFinal =  $array[2].'-'.$array[1].'-'.$array[0];
      return $dataFinal;
    }


    function formataDataRetono($data) {

      $array = explode('T', $data);

      $arrayD = explode('-', $array[0]);
      $dataFinal =  $arrayD[2].'/'.$arrayD[1].'/'.$arrayD[0].' '. substr($array[1], 0, -1);
      return $dataFinal;
    }


    function formataValor($valor) {

      $valor = str_replace('.', '', $valor);
      $valor = str_replace(',', '.', $valor);


      return number_format($valor, 2, '.', '');
    } 


    function formataCurrency($valor){

        $valor = $valor==''?0:$valor;
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);

        $formatter = new NumberFormatter('pt_BR',  NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($valor, 'BRL');


    } 

    function trataItemVazio($valor) {
      if (empty((array) $valor)) 
        $valor = "";
            
      return $valor;
    } 
}
