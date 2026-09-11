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
    //arquivo de retorno do banco (o numeronota antigo, usado direto, podia passar de
    //20 caracteres). Extrai o maior sufixo numerico ja usado e soma 1.
    public function proximoSeuNumeroBoleto()
    {
        $sql = "SELECT NVL(MAX(TO_NUMBER(SUBSTR(SEU_NUMERO_BOLETO, 5))), 0) + 1 AS PROXIMO
                FROM SCOTT.TAB_NOTAS_EMITIDAS
                WHERE SEU_NUMERO_BOLETO LIKE 'STER%'";

        //essa conexao (PDO/oci8 via yajra) forca PDO::ATTR_CASE_LOWER, entao a coluna
        //volta minuscula mesmo com "AS PROXIMO" na query - diferente da BaseRepository
        //(oci_* cru), que preserva o case padrao do Oracle (maiusculo)
        $result = DB::select(DB::raw($sql));
        $proximo = (int) $result[0]->proximo;

        return 'STER' . str_pad($proximo, 6, '0', STR_PAD_LEFT);
    }

    //grava o "seu numero" gerado na nota correspondente, pra a tela de Baixa (retorno
    //bancario) conseguir localizar a nota mesmo quando o retorno traz STER000001 em
    //vez do numeronota
    public function salvarSeuNumeroBoleto($numeronota, $seuNumero)
    {
        $sql = "UPDATE SCOTT.TAB_NOTAS_EMITIDAS SET SEU_NUMERO_BOLETO = ? WHERE NUMERONOTA = ?";

        return DB::update($sql, [$seuNumero, $numeronota]);
    }
}
