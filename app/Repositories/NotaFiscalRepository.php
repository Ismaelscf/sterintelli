<?php
namespace App\Repositories;
use Illuminate\Support\Facades\DB;

class NotaFiscalRepository
{
    public function buscarNF($nf)
    {
        $sql = "SELECT * 
            FROM SCOTT.TAB_NOTAS_EMITIDAS T 
            LEFT JOIN SCOTT.CLIENTES C ON C.CODIGO = T.CODCLIENTE 
            WHERE T.NUMERONOTA = '$nf'";

        $result = DB::select(DB::raw($sql));

        return $result[0];
    }
}
