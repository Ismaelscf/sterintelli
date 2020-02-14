<?php
namespace App\Repositories;

class NotaRepository extends BaseRepository
{

    public function buscaNotas(){

    	$sql = "select id, nome, nome_completo from tab_operadores t  where rownum < 4";

    	$this->executaSql($sql);

    }

    public function buscaDadosEmissao($id, $dtIni, $dtFim){


    	$sql = " SELECT 

    				   cnpj CNPJ_TOMADOR, 
    				   uf, 
    				   fantasia FANTASIA_TOMADOR, 
    				   clicod, endereco END_TOMADOR, 
				       municipio, 
				       bairro BAIRRO_TOMADOR, 
				       '65000000' CEP_TOMADOR,
				       numero NUM_TOMADOR,
				       nvl(im, '0000000') IM_TOMADOR, 
				       email EMAIL_TOMADOR,
				       MSGNF,
				       to_char(SUM(TOTAL),'99G999G999D99') AS TOTAL, 
				       to_char(SUM(TOTALD),'99G999G999D99') AS TOTAL_C_DESC, 
				       to_char(SUM(TRANSPORTE),'99G999G999D99') TRANSPORTE, 
				       to_char(NVL(SUM(DESCONTO),0),'99G999G999D99') DESCONT, 
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
				       to_char(SUM(TOTALD),'99G999G999D99') AS VAL_IR,
				       5 ALQ_ISS,
				       to_char(ROUND(SUM(TOTALD)*0.05, 2),'99G999G999D99') AS VAL_ISS,
				       1 QUANTIDADE
				FROM 		VIE_NF
				WHERE 		DATAESTE >= to_date('".$dtIni."', 'dd/mm/yyyy')
				AND 		DATAESTE <= to_date('".$dtFim."', 'dd/mm/yyyy')
				AND 		CLICOD=".$id."
				group by 	cnpj, uf, fantasia, clicod, endereco, 
					       	municipio, bairro, numero,
					       	ie, im, MSGNF, email";

		//echo $sql;
    	$this->executaSql($sql);
    	return $this->data[0];
    }


    public function buscaDadosEmissor($ambiente = '2'){

    	$sql = "SELECT 	CNPJ, IM, razaosocial, token, 
    					desc_servico,  cod_atividade,
  						desc_atividade, cod_servico  ,
  						desc_cod_servico, endereco,
  						'' email, telefone, cmun,  municipio,  uf
    			from TAB_NOTA_EMISSOR
    			where ambiente  = ".$ambiente;
				       
    	$this->executaSql($sql);
    	return $this->data[0];

    }


}
