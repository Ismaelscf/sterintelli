<?php

namespace App\Repositories;
use Illuminate\Support\Facades\DB;

class NotaRepository extends BaseRepository
{

	public function buscaDadosIniciais()
	{

		$dados = [];


		$sql = "select 0 tot_clientes, count(t.numero_nfse) qtd_notas,
				       sum(t.valornota) tot_notas
				from tab_notas_emitidas t
				where t.numero_nfse is not null
				and to_number(to_char(t.dtanota,'yyyymm')) >= to_number(to_char(SYSDATE, 'yyyymm') )";

		$this->executaSql($sql);
		if ($this->count > 0) {
			$dados['qtd_notas'] = $this->data[0]->QTD_NOTAS;
			$dados['tot_notas'] = $this->data[0]->TOT_NOTAS;
		} else {
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
		if ($this->count > 0) {
			$dados['qtd_clientes'] = $this->data[0]->QTD_CLIENTES;
			$dados['mes'] = $this->data[0]->MES;
		} else {
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
		if ($this->count > 0) {
			$dados['dados_grafico'] = $this->data;
		} else {
			$dados['dados_grafico'] = [];
		}


		return $dados;
	}

	public function buscaDadosEmissao($id, $dtIni, $dtFim, $searchOption, $tipoEste = 0)
	{

		$sqlBase = " SELECT
    				   cnpj CNPJ_TOMADOR,
    				   fn_limpa_char(fantasia) FANTASIA_TOMADOR,
    				   fn_limpa_char(CLIENTE) NOME_TOMADOR,
    				   clicod,
    				   SUBSTR(fn_limpa_char(min(endereco)),0,50) END_TOMADOR,
				       min(uf) uf, min(municipio) municipio,
				       nvl(min(municipio_id), 211130) municipio_id,
				       min(bairro) BAIRRO_TOMADOR,
				       replace(nvl(min(CEP), '65000000'), '-', '') CEP_TOMADOR,
				       min(numero) NUM_TOMADOR,
				       LPAD(nvl(decode(min(municipio_id), 2111300, decode(min(im), 'ISENTO', null, min(im)), null), '000000'),6,'0') IM_TOMADOR,
				       min(email) EMAIL_TOMADOR,
				       'SERVICOS DE ESTERILIZACAO DE MATERIAIS MEDICO - HOSPITALARES CONFORME LISTA ANEXA. CASO NÃO CONSIGA PAGAR ATE O VENCIMENTO ENTRAR EM CONTATO COM A EMPRESA, POIS A NOTA ESTÁ COM REGISTRO EM CARTORIO.\nPERIODO: " . $dtIni . " a " . $dtFim . "\nVENCIMENTO:  ' || '11/'|| to_char(sysdate, 'mm/yyyy') MSGNF,
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
				       to_char(per_ir) ALQ_IR,
				       to_char(ROUND(SUM(TOTALD)*(per_ir/100), 2),'99G999G990D99') AS VAL_IR,
				       5 ALQ_ISS,
				       to_char(ROUND(SUM(TOTALD)*0.05, 2),'99G999G990D99') AS VAL_ISS,
				       1 QUANTIDADE,
				       to_char(sysdate, 'dd/mm/yyyy') as dtanota,
				       '11/'|| to_char(sysdate, 'mm/yyyy') dtavencimento,
				       tipo_recolhimento
				FROM 		%s
				WHERE 		DATAESTE >= to_date('" . $dtIni . "', 'dd/mm/yyyy')
				AND 		DATAESTE <= to_date('" . $dtFim . "', 'dd/mm/yyyy')
				%s
				
				group by 	cnpj, fantasia, CLIENTE, clicod, 
					       	 tipo_recolhimento, per_ir";

		$sqlWhere = "";
		$table = " VIE_NF ";
		switch ($searchOption) {
			case 'fantasia':
				$sqlWhere = "AND 		CLICOD=" . $id . "";
				break;

			case 'razaoSocial':
				$sqlWhere = "AND 		CNPJ='" . $id . "'";
				$table = " VIE_NF_CNPJ ";
				break;

			case 'tipoEsterilizacao':
				$sqlWhere = " AND 		CLICOD= " . $id . "";
				$sqlWhere .= " AND 		TIPO_EST = $tipoEste ";
				$table = " VIE_NF_TIPO_EST";
				break;
		}

		$sql = sprintf($sqlBase, $table, $sqlWhere);
		// dd($sql);



		// print($sql); die;
		$this->executaSql($sql);
		return $this->data[0];
	}

	public function buscaDadosEmissaoEmBranco($id, $dtIni, $dtFim, $searchOption, $tipoEste = 0)
	{


		$sqlBase = " SELECT
    				   cnpj CNPJ_TOMADOR,
    				   fn_limpa_char(fantasia) FANTASIA_TOMADOR,
    				   fn_limpa_char(CLIENTE) NOME_TOMADOR,
    				   clicod,
    				   SUBSTR(fn_limpa_char(endereco),0,50) END_TOMADOR,
				       uf, municipio,
				       nvl(municipio_id, 211130) municipio_id,
				       bairro BAIRRO_TOMADOR,
				       replace(nvl(CEP, '65000000'), '-', '') CEP_TOMADOR,
				       numero NUM_TOMADOR,
				       LPAD(nvl(decode(municipio_id, 2111300, decode(im, 'ISENTO', null, im), null), '000000'),6,'0') IM_TOMADOR,
				       email EMAIL_TOMADOR,
				       'SERVICOS DE ESTERILIZACAO DE MATERIAIS MEDICO - HOSPITALARES CONFORME LISTA ANEXA. CASO NÃO CONSIGA PAGAR ATE O VENCIMENTO ENTRAR EM CONTATO COM A EMPRESA, POIS A NOTA ESTÁ COM REGISTRO EM CARTORIO.PERIODO: " . $dtIni . " a " . $dtFim . " VENCIMENTO:  ' || '10/'|| to_char(ADD_MONTHS(sysdate,1), 'mm/yyyy') MSGNF,
				       to_char(0,'99G999G990D99') AS TOTAL,
				       to_char(0,'99G999G990D99') AS TOTAL_C_DESC,
				       to_char(0,'99G999G990D99') TRANSPORTE,
				       to_char(0,'99G999G990D99') DESCONT,
				       0IMPOSTO,
				       '0,65' ALQ_PIS,
				       to_char(0,'99G999G990D99') AS  VAL_PIS,
				       3 ALQ_CONFINS,
				       to_char(0,'99G999G990D99') as VAL_CONFINS,
				       0 ALQ_INSS,
				       0 VAL_INSS,
				       1 ALQ_CSLL ,
				       to_char(0,'99G999G990D99') VAL_CSLL ,
				       to_char(per_ir) ALQ_IR,
				       to_char(0,'99G999G990D99') AS VAL_IR,
				       5 ALQ_ISS,
				       to_char(0,'99G999G990D99') AS VAL_ISS,
				       1 QUANTIDADE,
				       to_char(sysdate, 'dd/mm/yyyy') as dtanota,
				       '10/'|| to_char(ADD_MONTHS(sysdate,1), 'mm/yyyy') dtavencimento,
				       tipo_recolhimento
				FROM 		%s
				WHERE 		1 = 1
				%s ";


		$sqlWhere = "";
		$table = " VIE_NF ";
		switch ($searchOption) {
			case 'fantasia':
				$sqlWhere = "AND 		CLICOD=" . $id . "";
				break;

			case 'razaoSocial':
				$sqlWhere = "AND 		CNPJ='" . $id . "'";
				$table = " VIE_NF_CNPJ ";
				break;
		}

		$sql = sprintf($sqlBase, $table, $sqlWhere);


		//print($sql); die; 
		$this->executaSql($sql);
		return $this->data[0];
	}


	public function buscaIbgePorSiafi($codSiafi)
	{

		//comparacao numerica (nao por string) para nao depender de zeros a esquerda
		//no valor recebido do formulario
		$sql = "select cod_ibge from tab_municipio where cod_siafi = " . (int) $codSiafi;

		$this->executaSql($sql);
		if ($this->count > 0)
			return $this->data[0]->COD_IBGE;
		else
			return null;
	}

	public function buscaDadosEmissor($ambiente = '2')
	{

		$sql = "SELECT 	CNPJ, IM, razaosocial, token,
    					fn_limpa_char(desc_servico) as desc_servico,  cod_atividade,
  						desc_atividade, cod_servico  ,
  						desc_cod_servico, endereco,
  						'' email, telefone, cmun,  municipio,  uf
    			from TAB_NOTA_EMISSOR
    			where ambiente  = " . $ambiente;

		$this->executaSql($sql);
		return $this->data[0];
	}


	public function verificaNotaEmitida($numeroNota)
	{

		$sql = "select * from
    			tab_notas_emitidas
    			where NUMERONOTA = '" . $numeroNota . "'";

		$this->executaSql($sql);

		if ($this->count > 0)
			return true;
		else
			return false;
	}

	public function buscaNotaEmitida($idCliente, $dtIni, $dtFim)
	{

		$sql = "select numeronota, nvl(numero_nfse, 0) numero_nfse,
    				   nvl(codigoverificacao, 0) codigoverificacao,
    				   dtanota, valornota
				from tab_notas_emitidas t
				where t.codcliente = " . $idCliente . "
				and t.dtainicial = to_date('" . $dtIni . "', 'dd/mm/yyyy')
				and t.dtafinal = to_date('" . $dtFim . "', 'dd/mm/yyyy')";


		$this->executaSql($sql);
		return $this->data;
	}


	public function buscaNotaEmitidaPorNota($numeroNota)
	{

		$sql = "select codcliente, numeronota, 
						TO_CHAR(valornota, 'fm99G999G999G999D00') valornota, valornotadesc, valordesconto, 
						to_char(dtainicial, 'dd/mm/yyyy') dtainicial, to_char(dtafinal, 'dd/mm/yyyy') dtafinal, 
						to_char(dtanota, 'dd/mm/yyyy') dtanota, to_char(dtavencimento, 'dd/mm/yyyy') dtavencimento, 
						status,
						to_char(valpago, 'fm99G999G999G999D00') as valpago, dtapago, perc_iss, numero_nfse, codigoverificacao,
						chave_nfse, descricaonota, valorpis, valorcofins, valorinss,
						valorir, valorcsll, uf_tomador, municipio_id_tomador, tab_notas_emitidas.per_ir,
						ref_focus, status_focus, url_danfse, caminho_xml,
						c.NOME cliente, c.cnpj cnpj_cliente, c.UF as uf_cliente,
						c.FANTASIA fantasia_cliente, c.cep cep_cliente,
						c.endereco endereco_cliente,   m.nome municipio_cliente,
						c.municipio_id, m.cod_siafi,
						c.bairro bairro_cliente, c.email email_cliente,
						c.NUMERO numero_cliente, c.ie IE_cliente, c.IM IM_cliente
				from tab_notas_emitidas
				inner join clientes c on c.codigo = codcliente
				left join tab_municipio m on c.municipio_id = m.cod_ibge
				where numeronota = '" . $numeroNota . "'";


		$this->executaSql($sql);
		if ($this->count > 0)
			return $this->data[0];
		else
			return null;
	}

	public function buscaDadosNotaNfse($numNfse)
	{

		$sql = "select CODCLIENTE, NUMERONOTA,VALORNOTA,
						VALORNOTADESC,VALORDESCONTO,
						to_char(DTAINICIAL, 'dd/mm/yyyy') DTAINICIAL,
    					to_char(DTAFINAL, 'dd/mm/yyyy') DTAFINAL,
    					to_char(DTANOTA, 'dd/mm/yyyy') DTANOTA,
    					STATUS, VALPAGO,
    					to_char(DTAVENCIMENTO, 'dd/mm/yyyy') DTAVENCIMENTO,
						to_char(DTAPAGO, 'dd/mm/yyyy') DTAPAGO,
						t.PERC_ISS,  NUMERO_NFSE, nvl(t.PER_IR, 1.5) PER_IR,
						CODIGOVERIFICACAO,CHAVE_NFSE,
						REF_FOCUS, STATUS_FOCUS, URL_DANFSE, CAMINHO_XML,
						DESCRICAONOTA,
						VALORPIS,  VALORCOFINS,  VALORINSS, VALORIR,
						VALORCSLL,  UF_TOMADOR,
						MUNICIPIO_ID_TOMADOR,
						m.nome NOMEMUNICIPIO_TOMADOR, m.UF UF_TOMADOR,
						to_char(DTAINICIAL, 'mm/yyyy') mes_servico,
						decode(clientes.tipo_recolhimento, 'A', 'PRÓPRIO', 'R', 'RETIDO') AS TIPO_RECOLHIMENTO,
						clientes.email
        		from tab_notas_emitidas t
        		left join tab_municipio m on t.municipio_id_tomador = m.cod_siafi
				inner join CLIENTES on CODCLIENTE = clientes.codigo
				where t.numero_nfse = " . $numNfse . "";

		//print($sql);die;
		$this->executaSql($sql);
		if ($this->count > 0)
			return $this->data[0];
		else
			return null;
	}


	public function salvaNotaEmitida($dados)
	{


		$dados = json_decode(json_encode($dados));

		try {
			//checar se nota ja existe
			$sql = "insert into tab_notas_emitidas (
					CODCLIENTE, NUMERONOTA, VALORNOTA, DTAINICIAL, DTAFINAL,
					DTANOTA,STATUS,PERC_ISS,NUMERO_NFSE,CODIGOVERIFICACAO,
					CHAVE_NFSE, DESCRICAONOTA, DTAVENCIMENTO,
					VALORPIS, VALORCOFINS, VALORINSS, VALORIR, VALORCSLL,
					UF_TOMADOR, MUNICIPIO_ID_TOMADOR, PER_IR,
					REF_FOCUS, STATUS_FOCUS, URL_DANFSE, CAMINHO_XML
					)
					values
					(" . $dados->idcliente . ",
					'" . $dados->numeroNota . "',
					" . $dados->valorNota .
				",to_date('" . $dados->dtIni . "', 'dd/mm/yyyy'),
					to_date('" . $dados->dtFim . "', 'dd/mm/yyyy'),
					to_date('" . $dados->dataemissaorps . "', 'dd/mm/yyyy'),
					" . $dados->status .
				"," . $dados->percIss . "," . $dados->numeroNFe . ",'" .
				$dados->codigoVerificacao . "',
					'" . str_replace("'", "", $dados->chaveNfse) . "',
					'" . str_replace("'", "", $dados->descricaoNota) . "',
					to_date('" . $dados->dtVencimento . "', 'dd/mm/yyyy'),
					" . $dados->valorpis . ",
					" . $dados->valorcofins . ",
					" . $dados->valorinss . ",
					" . $dados->valorir . ",
					" . $dados->valorcsll . ",
					'" . $dados->estadoTomador . "',
					" . $dados->municipioIdTomador . "," . $dados->percIR . ",
					'" . $dados->refFocus . "',
					'" . $dados->statusFocus . "',
					'" . str_replace("'", "", $dados->urlDanfse) . "',
					'" . str_replace("'", "", $dados->caminhoXml) . "'
				)";

			$this->executaSql($sql);

			return [true, "Inserido com sucesso."];
		} catch (\Exception $e) {

			return [false, $e->getMessage()];
		}
	}

	public function atualizaStatusFocus($numeroNota, $statusFocus, $urlDanfse, $caminhoXml)
	{

		try {
			$sql = "update tab_notas_emitidas
					set status_focus = '" . $statusFocus . "',
						url_danfse = '" . str_replace("'", "", $urlDanfse) . "',
						caminho_xml = '" . str_replace("'", "", $caminhoXml) . "'
					where numeronota = '" . $numeroNota . "'";

			$this->executaSql($sql);

			return [true, "Status atualizado com sucesso."];
		} catch (\Exception $e) {

			return [false, $e->getMessage()];
		}
	}

	public function consultarNotasEmitidas($dtIni, $dtFim, $idCliente)
	{

		$sql = "select t.codcliente, cnpj, nome, t.numeronota, valornota,
				       to_char(t.dtaInicial, 'dd/mm/yyyy') dtaInicial,
				       to_char(t.dtafinal, 'dd/mm/yyyy') dtafinal,
				       to_char(t.dtanota, 'dd/mm/yyyy') dtanota,
				       to_char(t.dtapago, 'dd/mm/yyyy') dtapago,
				       t.valpago,
				       to_char(t.dtavencimento, 'dd/mm/yyyy') dtavencimento,
					   t.numero_nfse, t.codigoverificacao,
					   to_char(b.dtaemissao, 'dd/mm/yyyy') dtaboleto
				from tab_notas_emitidas t
				inner join clientes c on t.codcliente = c.codigo
				left join  tab_boletos_emitidos b on t.codcliente = b.codcliente and t.numeronota = b.numeronota
				where t.dtanota between to_date('" . $dtIni . "', 'dd/mm/yyyy')
				and to_date('" . $dtFim . "', 'dd/mm/yyyy')";

		if ($idCliente > 0)
			$sql .= " and t.codcliente = " . $idCliente . " ";

		//dd($sql);
		$this->executaSql($sql);
		return $this->data;
	}


	public function buscaDetalheNota($idCliente, $idNota)
	{

		$sql = "select codcliente, cnpj, nome razao, numeronota, valornota,
				       to_char(dtaInicial, 'dd/mm/yyyy') dtaInicial,
				       to_char(dtafinal, 'dd/mm/yyyy') dtafinal,
				       to_char(dtanota, 'dd/mm/yyyy') dtanota,
				       to_char(dtapago, 'dd/mm/yyyy') dtapago,
				       valpago,
				       to_char(dtavencimento, 'dd/mm/yyyy') dtavencimento
				from tab_notas_emitidas t
				inner join clientes c on t.codcliente = c.codigo
				where numeronota = '$idNota'
				and codcliente = $idCliente";


		$this->executaSql($sql);
		return $this->data[0];
	}

	public function salvaDetalheNotaEmitida($dados)
	{

		$dados = json_decode(json_encode($dados));

		try {
			$sql = "update 	tab_notas_emitidas
	    			set 	dtapago = to_date('" . $dados->dataPago . "', 'dd/mm/yyyy'),
							valpago = " . $dados->valorPago . "
					where 	codcliente = " . $dados->idcliente . "
					and 	numeronota = '" . $dados->numeroNota . "'";

			$this->executaSql($sql);

			return [true, "Nota editada com sucesso."];
		} catch (\Exception $e) {

			return [false, $e->getMessage()];
		}
	}

	public function consultaFaturamentoNota($dtIni, $dtFim, $idCliente)
	{

		return $this->consultaFaturamento($dtIni, $dtFim, $idCliente, 'fantasia');
	}

	public function consultaFaturamentoNotaItens($dtIni, $dtFim, $idCliente)
	{

		return $this->consultaFaturamentoItens($dtIni, $dtFim, $idCliente, 'fantasia');
	}

	public function consultar_notas_vencidas(){
		try {
			$sql = "
				SELECT 
					C.CODIGO,
					C.NOME,
					E.NUMERONOTA, 
					to_char(E.DTANOTA, 'dd/mm/yyyy') AS dataNota, 
					to_char(E.DTAPAGO, 'dd/mm/yyyy') AS dataPago,
					E.VALORNOTA,
					E.VALPAGO,
					round(TRUNC(SYSDATE) - TRUNC(E.DTANOTA), 1) AS diasEmissao, 
					to_char(E.DTAVENCIMENTO, 'dd/mm/yyyy') AS dataVencimento,
					C.EMAIL,
					E.BOL_ENVIO_EMAIL
				FROM 
					SCOTT.TAB_NOTAS_EMITIDAS E
				JOIN 
					SCOTT.CLIENTES C ON C.CODIGO = E.CODCLIENTE 
				WHERE 
					E.DTAPAGO IS NULL
					AND E.BOL_ENVIO_EMAIL = 'N'
					AND E.DTANOTA > TO_DATE('01/10/2024', 'dd/mm/yyyy')
					AND E.DTAVENCIMENTO + 2 < SYSDATE
					AND ROWNUM <= 2
				ORDER BY 
					E.DTANOTA DESC
			";
	
			$dados = DB::select(DB::raw($sql));
			// dd($dados);
			return $dados;
		} catch (\Exception $e) {
			return [false, $e->getMessage()];
		}
	}
	
	public function atualizar_status_envio_cobranca($dados, $status)
	{
		try {
			$updated = DB::update("
				UPDATE SCOTT.TAB_NOTAS_EMITIDAS 
				SET BOL_ENVIO_EMAIL = :status
				WHERE NUMERONOTA = :numeroNota
				AND CODCLIENTE = :codigoCliente
			", [
				'status' => $status,
				'numeroNota' => $dados->numeronota,
				'codigoCliente' => $dados->codigo
			]);

			// Retorna um resultado de sucesso ou falha da atualização
			if ($updated) {
				return 'Status atualizado com sucesso';
			} else {
				return 'Nenhuma linha foi atualizada';
			}
		} catch (\Exception $e) {
			// Retorna a mensagem de erro se ocorrer uma exceção
			return 'Erro ao atualizar o status: ' . $e->getMessage();
		}
	}

	public function salvar_envio_cobranca($dados)
	{
		try {
			$inserted = DB::insert("
				INSERT INTO SCOTT.ENVIO_EMAIL_COBRANCA (CODIGO_CLIENTE, NOME_CLIENTE, EMAIL_CLIENTE, NF, VALOR, DATA_ENVIO) 
				VALUES (:codigoCliente, :nomeCliente, :emailCliente, :numeroNota, :valor, SYSDATE)
			", [
				'codigoCliente' => $dados->codigo,
				'nomeCliente' => $dados->nome,
				'emailCliente' => $dados->email,
				'numeroNota' => $dados->numeronota,
				'valor' => $dados->valornota
			]);
	
			if ($inserted) {
				return 'Registro inserido com sucesso';
			} else {
				return 'Falha ao inserir o registro';
			}
		} catch (\Exception $e) {
			return 'Erro ao inserir o registro: ' . $e->getMessage();
		}
	}
	
	

}
