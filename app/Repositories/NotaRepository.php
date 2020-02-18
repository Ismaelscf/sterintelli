<?php
namespace App\Repositories;

class NotaRepository extends BaseRepository
{

    /*public function buscaNotas(){

    	$sql = "select id, nome, nome_completo from tab_operadores t  where rownum < 4";

    	$this->executaSql($sql);

    }*/

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


    public function verificaNotaEmitida($numeroNota){

    	$sql = "select * from 
    			tab_notas_emitidas 
    			where NUMERONOTA = '".$numeroNota."'";

    	$this->executaSql($sql);
    	
    	if ($this->count > 0)
    		return true;
    	else
    		return false;

    }

    public function buscaNotaEmitida($idCliente, $dtIni, $dtFim){

    	$sql = "select numeronota, nvl(numero_nfse, 0) numero_nfse, 
    				   nvl(codigoverificacao, 0) codigoverificacao, 
    				   dtanota, valornota
				from tab_notas_emitidas t
				where t.codcliente = ".$idCliente."
				and t.dtainicial = to_date('".$dtIni."', 'dd/mm/yyyy')
				and t.dtafinal = to_date('".$dtFim."', 'dd/mm/yyyy')";
				       

    	$this->executaSql($sql);
    	return $this->data;

    }

    public function buscaDadosNotaNfse($numNfse){

    	$sql = "select CODCLIENTE, NUMERONOTA, VALORNOTA, 
    					to_char(DTAINICIAL, 'dd/mm/yyyy') DTAINICIAL,
    					to_char(DTAFINAL, 'dd/mm/yyyy') DTAFINAL
				from tab_notas_emitidas t
				where t.numero_nfse = ".$numNfse."";
				       
    	$this->executaSql($sql);
    	return $this->data[0];

    }


    public function salvaNotaEmitida($idcliente, $numeroNota, 
          $valorNota, $dtIni, $dtFim, $status,
          $dataemissaorps, $percIss, $numeroNFe, 
          $codigoVerificacao, $chaveNfse){

    	try{
	    	//checar se nota ja existe
	    	$sql = "insert into tab_notas_emitidas (
					CODCLIENTE, NUMERONOTA, VALORNOTA, DTAINICIAL, DTAFINAL,
					DTANOTA,STATUS,PERC_ISS,NUMERO_NFSE,CODIGOVERIFICACAO,
					CHAVE_NFSE)
					values
					(".$idcliente.",'".$numeroNota."',".$valorNota.
					",to_date('".$dtIni."', 'dd/mm/yyyy'),
					to_date('".$dtFim."', 'dd/mm/yyyy'),
					to_date('".$dataemissaorps."', 'dd/mm/yyyy'),".$status.
					",".$percIss.",".$numeroNFe.",'".
					$codigoVerificacao."','".str_replace("'", "", $chaveNfse)."')";

			//echo $sql;
			$this->executaSql($sql);

		return [true,"Inserido com sucesso."];

        } catch (\Exception $e) {

            return [false, $e->getMessage()];
        }
	}


	public function consultarNotasEmitidas($dtIni, $dtFim, $idCliente){

    	$sql = "select codcliente, nome, numeronota, valornota,
				       dtaInicial, dtafinal, dtanota, 
				       dtapago, valpago, dtavencimento
				from tab_notas_emitidas t
				inner join clientes c on t.codcliente = c.codigo
				where t.dtainicial = to_date('".$dtIni."', 'dd/mm/yyyy')
				and t.dtafinal = to_date('".$dtFim."', 'dd/mm/yyyy')";

		if($idCliente > 0)
			$sql .= " and t.codcliente = ".$idCliente." ";
				       

    	$this->executaSql($sql);
    	return $this->data;

    }

    public function consultaFaturamentoNota($dtIni, $dtFim, $idCliente){
    	$sql = " SELECT  
    				c.nome, 
					FANTASIA, 
					n.cliente as clicod,
					to_char(round(sum(TOTALD),2), 'FM999G999G999D90') totald, 
					to_char(nvl(round(sum(TOTAL) * (n.DESCONTO/100),2), 0), 'FM999G999G999D90') DESCONTO,
			        to_char(round(sum(TRANSPORTE),2), 'FM999G999G999D90') transporte,
			        to_char(round(sum(TOTAL),2), 'FM999G999G999D90') total, 
			        to_char(round(sum(TOTALDESC),2), 'FM999G999G999D90') TOTALDESC
				FROM NOTA_TOTAL3 n
				inner join clientes c on n.cliente = c.codigo 
				where  DATAESTE BETWEEN to_date('".$dtIni."', 'dd/mm/yyyy') 
					   AND to_date('".$dtFim."', 'dd/mm/yyyy')
				and n.cliente = $idCliente
				group by CLIENTE, FANTASIA, n.cliente, nascimento, n.desconto, nome
					order by fantasia";



    	$this->executaSql($sql);
    	return $this->data[0];
    }


    public function consultaFaturamentoNotaItens($dtIni, $dtFim, $idCliente){
		$sql = " SELECT nome,  
				to_char(UNITARIO, 'FM999G999G999D90') val_unitario, 
				sum(QUANTIDADE) qtd, 
				to_char(sum(TOTAL), 'FM999G999G999D90') total
				from vie_itens_nota3
				where DATAESTE BETWEEN to_date('".$dtIni."', 'dd/mm/yyyy')  
				AND to_date('".$dtFim."', 'dd/mm/yyyy')  
				and CLIENTE = $idCliente
				group by 
				CLIENTE, SERVICO, UNITARIO, UNITARIOD, nome
				order by nome";

    	$this->executaSql($sql);
    	return $this->data;
    }

}
