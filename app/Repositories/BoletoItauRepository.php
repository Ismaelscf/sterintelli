<?php
namespace App\Repositories;
use Illuminate\Support\Facades\DB;
use App\Model\BoletoItau;

class BoletoItauRepository
{

    
    public function salvarDadosBoleto($id_beneficiario, $nossoNumero, $numeronota)
    {
        $sql = "INSERT INTO TB_BOLETO_ITAU (NOTAFISCAL, ID_BENEFICIARIO, CARTEIRA, DATA_INCLUSAO, NOSSO_NUMERO) VALUES (?, ?, ?, SYSDATE, ?)";

        $save = DB::insert($sql, [
            $numeronota,
            $id_beneficiario,
            109,
            $nossoNumero
        ]);

        return $save;
    }

    public function consultarBoletosPorNF($nf)
    {
        $sql = "SELECT * FROM TB_BOLETO_ITAU WHERE NOTAFISCAL = ?";
        $result = DB::select($sql, [(int) $nf]);

        return $result;
    }

    public function getLastNumero(){
        $nossoNumero = BoletoItau::select('NOSSO_NUMERO')->orderBy('NOSSO_NUMERO', 'desc')->first();
        return $nossoNumero;
    }

    public function buscarDadosBoleto($nossoNumero) {
        $boleto = BoletoItau::where('NOSSO_NUMERO', $nossoNumero)->first();
        return $boleto;
    }

    
}
