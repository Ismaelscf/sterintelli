<?php
namespace App\Repositories;

class FaturamentoRepository extends BaseRepository
{


	function consultarFatPeriodo($dataInicial, $dataFinal, $cliente, $estado, $municipio, $searchOption, $tipoEste = 0)
	{
		//vie_nota_detalhe gera uma linha por (cliente, dia, tipo_esterilizacao); como o
		//transporte e um valor fixo por dia (nao varia por tipo), um dia com mais de um
		//tipo de esterilizacao produz 2+ linhas com o MESMO transporte, e SUM(n.transporte)
		//conta esse valor mais de uma vez. A subquery "tr" abaixo remove essa duplicacao
		//(DISTINCT por cliente+dia+transporte antes de somar), e o MAX() no SELECT principal
		//so pega o total ja correto (repetido igualmente em todas as linhas do join), sem
		//somar de novo.
		//
		//A subquery "nf" monta o caminho usado pelo botao de imprimir NFSe (rota
		//notas/imprimirnfse/{numnota}/{codigo}/). NVL para 0 porque essa rota exige o
		//segmento {codigo} preenchido (vazio da 404 no Laravel) e o controller nao usa
		//esse valor quando ainda nao ha codigo de verificacao salvo, so busca por numnota
		//e reconsulta a Focus NFe ao vivo.
		$sqlBase = "SELECT c.nome AS razao, %s, SUM(n.qtd) AS qtd,
							TO_CHAR(SUM(n.totaldesc), '99G999G990D99') AS TOTAL,
							TO_CHAR(NVL(MAX(tr.transporte_total), 0), '99G999G990D99') AS TRANSPORTE,
							TO_CHAR(SUM(n.totaldesc) + NVL(MAX(tr.transporte_total), 0), '99G999G990D99') AS TOT_C_TRANSPORTE
					FROM vie_nota_detalhe n
					INNER JOIN clientes c ON n.clicod = c.codigo
					LEFT JOIN (
						SELECT clicod, SUM(transporte) transporte_total
						FROM (
							SELECT DISTINCT clicod, dataeste, transporte
							FROM vie_nota_detalhe
							WHERE DATAESTE BETWEEN TO_DATE('".$dataInicial."', 'dd/mm/yyyy') AND TO_DATE('".$dataFinal."', 'dd/mm/yyyy')
						)
						GROUP BY clicod
					) tr ON tr.clicod = n.clicod
					LEFT JOIN (
						SELECT codcliente, dtainicial, dtafinal,
							   '/' || numeronota || '/' || NVL(codigoverificacao, '0') || '/' AS caminho
						FROM (
							SELECT codcliente, dtainicial, dtafinal, numeronota, codigoverificacao,
								   ROW_NUMBER() OVER (
									 PARTITION BY codcliente, dtainicial, dtafinal
									 ORDER BY dtanota DESC,
									          CASE WHEN status_focus = 'autorizado' THEN 0 ELSE 1 END,
									          ROWID DESC
								   ) rn
							FROM tab_notas_emitidas
							WHERE numeronota IS NOT NULL
						)
						WHERE rn = 1
					) nf ON nf.codcliente = n.clicod
						AND nf.dtainicial = TO_DATE('".$dataInicial."', 'dd/mm/yyyy')
						AND nf.dtafinal = TO_DATE('".$dataFinal."', 'dd/mm/yyyy')
					%s
					WHERE DATAESTE BETWEEN TO_DATE('".$dataInicial."', 'dd/mm/yyyy') AND TO_DATE('".$dataFinal."', 'dd/mm/yyyy')";
	
		$sqlJoin = "";
		$sqlGroup = " GROUP BY %s ";
		$sqlOrder = " ORDER BY %s";
	
		// Define selection, grouping, and ordering based on the search option
		switch ($searchOption) {
			case 'fantasia':
				$fields = " c.fantasia, 0 AS TIPO_ESTE, 'Todos' AS desc_tipo_est, ";
				$fields .= " n.clicod, MAX(nf.caminho) AS caminho, c.email";

				$groupFields = " c.nome, c.fantasia, n.clicod, c.email";
				$orderField = " c.fantasia";
				break;

			case 'razaoSocial':
				$fields = " '' AS fantasia, 0 AS TIPO_ESTE, 'Todos' AS desc_tipo_est, ";
				$fields .= " c.CNPJ clicod, MAX(nf.caminho) AS caminho, min(c.email) as email";

				$groupFields = " c.nome, c.CNPJ";
				$orderField = " c.nome";
				break;

			case 'tipoEsterilizacao':
				$fields = " c.fantasia, n.TIPO_ESTE, te.descricao AS desc_tipo_est, ";
				$fields .= " n.clicod, MAX(nf.caminho) AS caminho, c.email";
	
				$sqlJoin = " INNER JOIN TAB_TIP_EST te ON n.TIPO_ESTE = te.id ";
				if ($tipoEste > 0) {
					$sqlWhere = " AND n.TIPO_ESTE = $tipoEste";
				}
	
				$groupFields = " c.nome, c.fantasia, n.TIPO_ESTE, te.descricao, n.clicod, c.email";
				$orderField = " c.fantasia, te.descricao";
				break;
	
			default:
				// Default case for safe failover
				$fields = " c.fantasia, 0 AS TIPO_ESTE, 'Todos' AS desc_tipo_est, ";
				$groupFields = " c.nome, c.fantasia";
				$orderField = " c.fantasia";
		}
	
		// Construct final SQL query
		$sql = sprintf($sqlBase, $fields, $sqlJoin) . 
				(isset($cliente) && $cliente != "-1" ? " AND n.clicod = $cliente" : "") .
				(isset($estado) && $estado != "-1" ? " AND c.codigo = n.clicod AND UPPER(c.UF) = '$estado'" : "") .
				(isset($municipio) && $municipio != "-1" ? " AND c.codigo = n.clicod AND UPPER(c.municipio) = '$municipio'" : "") .
				(isset($tipoEste) && $tipoEste > 0 ? " AND n.TIPO_ESTE = $tipoEste " : "") .
				sprintf($sqlGroup, $groupFields) .
				sprintf($sqlOrder, $orderField);
	
		// echo $sql; die;
		$this->executaSQL($sql);
		return $this->data;
	}
	


    function consultarDetFatPeriodo($dataInicial, $dataFinal, $cliente, $searchOption, $tipoEste= 0){
			

			$sqlGroup = " GROUP BY dataeste, ";
	
			switch ($searchOption) {
				case 'fantasia':
					$fields = " n.cliente codigo, c.fantasia, c.nome,  ";
	
					$sqlWhere = " and n.clicod = $cliente";
	
					$sqlGroup .= " c.nome, c.fantasia, n.cliente";

					break;
	
				case 'razaoSocial':
					$fields = " c.nome, c.nome FANTASIA, c.CNPJ as codigo, ";
								
					$sqlWhere = " and   c.CNPJ = '$cliente'";
					$sqlGroup .= " c.nome, c.CNPJ";

					break;
	
				case 'tipoEsterilizacao':
					$fields = " n.cliente codigo, c.fantasia, c.nome,  ";
	
					$sqlWhere = " and n.clicod = $cliente";
					
					if($tipoEste > 0)
						$sqlWhere .= " and  n.TIPO_ESTE = $tipoEste";
	
					$sqlGroup .= " c.nome, c.fantasia, n.cliente";
					break;
				}
	
			
			//como o grupo ja e por (dataeste, cliente), transporte e um valor fixo dentro do
			//grupo (nao varia por tipo_esterilizacao); usar MAX evita a mesma duplicacao de
			//SUM(n.transporte) explicada em consultarFatPeriodo, quando o dia tem mais de um
			//tipo_esterilizacao
			$sql = "select $fields
						  to_char(n.DATAESTE, 'dd/mm/yyyy') as dataeste,
						  sum(n.Qtd) qtd,
						  to_char(sum(n.TOTALDesc),'99G999G990D99') as total,
						  to_char(max(n.transporte),'99G999G990D99') as transporte,
						  to_char(sum(n.TOTALDesc) + max(n.transporte),'99G999G990D99') as TOT_C_TRANSPORTE
					from vie_nota_detalhe N
					inner join clientes c on n.clicod = c.codigo
					where DATAESTE BETWEEN to_date('".$dataInicial."', 'dd/mm/yyyy') AND to_date('".$dataFinal."', 'dd/mm/yyyy')
					$sqlWhere
					$sqlGroup
					order by DATAESTE asc ";	

			//echo $sql; die;
			$this->executaSQL($sql);
			return $this->data;
		}


}