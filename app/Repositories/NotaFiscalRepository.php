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

    //gera o proximo "seu numero" (texto_seu_numero, enviado a Itau na geracao do
    //boleto) no formato STER000001 - curto (10 caracteres) pra nao ser truncado no
    //arquivo de retorno do banco. So usado como fallback pra notas antigas que ainda
    //nao tem SEU_NUMERO_BOLETO (a partir de agora, o codigo ja nasce com a nota em
    //NotaController::posEmitir via NotaRepository::proximoCodigoVinculo).
    //
    //Usa a mesma sequence SEQ_CODIGO_VINCULO que proximoCodigoVinculo() - tem que ser a
    //MESMA sequence, senao os dois geradores podem devolver o mesmo numero um pro outro
    //(MAX+1 tinha exatamente esse problema de colisao, ver NotaRepository::
    //proximoCodigoVinculo).
    public function proximoSeuNumeroBoleto()
    {
        $result = DB::select(DB::raw("SELECT 'STER' || LPAD(SEQ_CODIGO_VINCULO.NEXTVAL, 6, '0') AS CODIGO FROM DUAL"));

        //PDO::ATTR_CASE_LOWER faz a coluna voltar minuscula mesmo com "AS CODIGO"
        return $result[0]->codigo;
    }

    //grava o "seu numero" gerado na nota correspondente, pra a tela de Baixa (retorno
    //bancario) conseguir localizar a nota mesmo quando o retorno traz STER000001 em
    //vez do numeronota. Grava tambem a data de cadastro do vinculo - a trigger so
    //preenche isso automaticamente em INSERT, e aqui e um UPDATE (nota ja existia)
    public function salvarSeuNumeroBoleto($numeronota, $seuNumero)
    {
        $sql = "UPDATE SCOTT.TAB_NOTAS_EMITIDAS SET SEU_NUMERO_BOLETO = ?, DTA_CADASTRO_VINCULO = SYSDATE WHERE NUMERONOTA = ?";

        return DB::update($sql, [$seuNumero, $numeronota]);
    }
}
