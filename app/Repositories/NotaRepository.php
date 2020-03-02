<?php
namespace App\Repositories;

class NotaRepository extends BaseRepository
{

	public function buscaDadosIniciais(){

		$dados = [];

		
		$sql = "select 0 tot_clientes, count(t.numero_nfse) qtd_notas,
				       sum(t.valornota) tot_notas
				from tab_notas_emitidas t
				where t.numero_nfse is not null
				and to_number(to_char(t.dtanota,'yyyymm')) >= to_number(to_char(SYSDATE, 'yyyymm') )";

		$this->executaSql($sql);
		if ($this->count > 0){
	    	$dados['qtd_notas'] = $this->data[0]->QTD_NOTAS;
	    	$dados['tot_notas'] = $this->data[0]->TOT_NOTAS;
	    }else{
    		$dados['qtd_notas'] = 0;
    		$dados['tot_notas'] = 0;
	    }


		$sql = " select count(t.codcliente) qtd_clientes, 
					to_char(t.dtanota,'mm/yyyy') mes
				from tab_notas_emitidas t
				where t.numero_nfse is not null
				and to_number(to_char(t.dtanota,'yyyymm')) >= to_number(to_char(SYSDATE, 'yyyymm') )
				group by to_char(t.dtanota,'mm/yyyy') ";

		$this->executaSql($sql);
		if ($this->count > 0){
	    	$dados['qtd_clientes'] = $this->data[0]->QTD_CLIENTES;
	    	$dados['mes'] = $this->data[0]->MES;
	   }else{
    		$dados['qtd_clientes'] = 0;
    		$dados['mes'] = date("m/Y");
	   }

		$sql = "select count(t.numero_nfse) qtd_notas,
				       sum(t.valornota) tot_notas,
				       to_char(t.dtanota,'mm/yyyy') as periodo 
				from tab_notas_emitidas t 
				where numero_nfse is not null
				and to_number(to_char(t.dtanota,'yyyymm')) >= to_number(to_char(SYSDATE-300, 'yyyymm') ) 
				group by to_char(t.dtanota,'mm/yyyy')";

		$this->executaSql($sql);
		if ($this->count > 0){
    		$dados['dados_grafico'] = $this->data;
    	}else{
    		$dados['dados_grafico'] = [];
    	}


    	return $dados;

	}

    public function buscaDadosEmissao($id, $dtIni, $dtFim){


    	$sql = " SELECT 

    				   cnpj CNPJ_TOMADOR, 
    				   uf, 
    				   fantasia FANTASIA_TOMADOR, 
    				   CLIENTE NOME_TOMADOR, 
    				   clicod, endereco END_TOMADOR, 
				       municipio, 
				       bairro BAIRRO_TOMADOR, 
				       nvl(CEP, '65000000') CEP_TOMADOR,
				       numero NUM_TOMADOR,
				       nvl(im, '0000000') IM_TOMADOR, 
				       email EMAIL_TOMADOR,
				       'SERVIÇOS DE ESTERILIZAÇÃO DE MATERIAIS MÉDICO - HOSPITALARES CONFORME LISTA ANEXA. CASO NÃO CONSIGA PAGAR ATÉ O VENCIMENTO ENTRAR EM CONTATO COM A EMPRESA, POIS A NOTA ESTÁ COM REGISTRO EM CARTÓRIO.
PERÍODO: ".$dtIni." a ".$dtFim."
VENCIMENTO:  ' || to_char(sysdate,'mm/yyyy') MSGNF,
				       to_char(SUM(TOTAL),'99G999G990D99') AS TOTAL, 
				       to_char(SUM(TOTALD),'99G999G990D99') AS TOTAL_C_DESC, 
				       to_char(SUM(TRANSPORTE),'99G999G990D99') TRANSPORTE, 
				       to_char(NVL(SUM(DESCONTO),0),'99G999G990D99') DESCONT, 
				       sum(imposto) IMPOSTO,
				       '0,65' ALQ_PIS, 
				       to_char(ROUND(SUM(TOTALD)*0.0065, 2),'99G999G990D99') AS  VAL_PIS,
				       3 ALQ_CONFINS,
				       to_char(ROUND(SUM(TOTALD)*0.03, 2),'99G999G990D99') as VAL_CONFINS, 
				       0 ALQ_INSS,
				       0 VAL_INSS,
				       1 ALQ_CSLL ,
				       to_char(ROUND(SUM(TOTALD)*0.01, 2),'99G999G990D99') VAL_CSLL ,
				       '1,5' ALQ_IR,
				       to_char(ROUND(SUM(TOTALD)*0.015, 2),'99G999G990D99') AS VAL_IR,
				       5 ALQ_ISS,
				       to_char(ROUND(SUM(TOTALD)*0.05, 2),'99G999G990D99') AS VAL_ISS,
				       1 QUANTIDADE
				FROM 		VIE_NF
				WHERE 		DATAESTE >= to_date('".$dtIni."', 'dd/mm/yyyy')
				AND 		DATAESTE <= to_date('".$dtFim."', 'dd/mm/yyyy')
				AND 		CLICOD=".$id."
				group by 	cnpj, uf, fantasia, CLIENTE, clicod, endereco, 
					       	municipio, bairro, numero,
					       	ie, im, MSGNF, email, cep";
 
	
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
    	
    	return $this->consultaFaturamento($dtIni, $dtFim, $idCliente);
    }


    public function consultaFaturamentoNotaItens($dtIni, $dtFim, $idCliente){
	    	
    	return $this->consultaFaturamentoItens($dtIni, $dtFim, $idCliente);
    }

}
