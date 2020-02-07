<?php
namespace App\Repositories;

class NotaRepository extends BaseRepository
{

    public function buscaNotas(){

    	$sql = "select id, nome, nome_completo from tab_operadores t  where rownum < 4";

    	$this->executaSql($sql);

    }

    public function buscaDadosEmissao($id, $dtIni, $dtFim){

    	$sql = "SELECT cnpj, uf, fantasia, clicod, endereco, 
				       municipio, bairro, numero,
				       ie, im, MSGNF,
				       SUM(TOTAL) AS TOTAL, 
				       SUM(TOTALD) AS TOTAL_C_DESC, 
				       SUM(TRANSPORTE) TRANSPORTE, 
				       NVL(SUM(DESCONTO),0) DESCONT, 
				       sum(imposto) IMPOSTO,
				       ROUND(SUM(TOTALD)*0.015, 2) AS IR 
				FROM VIE_NF
				WHERE DATAESTE >= to_date('".$dtIni."')
				AND DATAESTE <= to_date('".$dtFim."')
				AND CLICOD=".$id."
				group by cnpj, uf, fantasia, clicod, endereco, 
				       municipio, bairro, numero,
				       ie, im, MSGNF";

		echo $sql;
    	$this->executaSql($sql);

    }



}
