<?php
namespace App\Repositories;

class NotaRepository extends BaseRepository
{

    public function buscaNotas(){

    	$sql = "select id, nome, nome_completo from tab_operadores t  where rownum < 4";

    	$this->executaSql($sql);

    }

    public function buscaDadosEmissao($id, $dtIni, $dtFim){


    	$sql = "SELECT cnpj CNPJ_TOMADOR, 
    				   uf, 
    				   fantasia FANTASIA_TOMADOR, 
    				   clicod, endereco END_TOMADOR, 
				       municipio, 
				       bairro BAIRRO_TOMADOR, 
				       '650000-000' CEP_TOMADOR,
				       numero NUM_TOMADOR,
				       im IM_TOMADOR, 
				       email EMAIL_TOMADOR,
				       MSGNF,
				       SUM(TOTAL) AS TOTAL, 
				       SUM(TOTALD) AS TOTAL_C_DESC, 
				       SUM(TRANSPORTE) TRANSPORTE, 
				       NVL(SUM(DESCONTO),0) DESCONT, 
				       sum(imposto) IMPOSTO,
				       '0,65' ALQ_PIS, 
				       0 VAL_PIS,
				       3 ALQ_CONFINS,
				       0 VAL_CONFINS, 
				       0 ALQ_INSS,
				       0 VAL_INSS,
				       1 ALQ_CSLL ,
				       0 VAL_CSLL ,
				       '1,5' ALQ_IR,
				       ROUND(SUM(TOTALD)*0.015, 2) AS VAL_IR,
				       5 ALQ_ISS,
				       ROUND(SUM(TOTALD)*0.05, 2) AS VAL_ISS,
				       1 QUANTIDADE
				FROM 		VIE_NF
				WHERE 		DATAESTE >= to_date('".$dtIni."', 'dd/mm/yyyy')
				AND 		DATAESTE <= to_date('".$dtFim."', 'dd/mm/yyyy')
				AND 		CLICOD=".$id."
				group by 	cnpj, uf, fantasia, clicod, endereco, 
					       	municipio, bairro, numero,
					       	ie, im, MSGNF, email";

    	$this->executaSql($sql);
    	return $this->data[0];
    }


    public function buscaDadosEmissor($ambiente = 'H'){

    	$sql = "SELECT CNPJ, IM, razaosocial, token, desc_servico 
    			from TAB_NOTA_EMISSOR
    			where ambiente  = '".$ambiente."'";
				       
    	$this->executaSql($sql);
    	return $this->data[0];

    }


}
