<?php
namespace App\Repositories;

class FaturamentoRepository extends BaseRepository
{


	function consultarFatPeriodo($dataInicial, $dataFinal, $cliente, $estado, $municipio, $searchOption, $tipoEste = 0)
	{
		$sqlBase = "SELECT c.nome AS razao, %s, SUM(n.qtd) AS qtd, 
							TO_CHAR(SUM(n.totaldesc), '99G999G990D99') AS TOTAL,
							TO_CHAR(SUM(n.transporte), '99G999G990D99') AS TRANSPORTE,
							TO_CHAR(SUM(n.totald), '99G999G990D99') AS TOT_C_TRANSPORTE
					FROM vie_nota_detalhe n
					INNER JOIN clientes c ON n.clicod = c.codigo
					%s
					WHERE DATAESTE BETWEEN TO_DATE('".$dataInicial."', 'dd/mm/yyyy') AND TO_DATE('".$dataFinal."', 'dd/mm/yyyy')";
	
		$sqlJoin = "";
		$sqlGroup = " GROUP BY %s ";
		$sqlOrder = " ORDER BY %s";
	
		// Define selection, grouping, and ordering based on the search option
		switch ($searchOption) {
			case 'fantasia':
				$fields = " c.fantasia, 0 AS TIPO_ESTE, 'Todos' AS desc_tipo_est, ";
				$fields .= " n.clicod, fn_busca_nfse(n.clicod, TO_DATE('".$dataInicial."', 'dd/mm/yyyy'), 
							TO_DATE('".$dataFinal."', 'dd/mm/yyyy')) AS caminho, c.email";
	
				$groupFields = " c.nome, c.fantasia, n.clicod, c.email";
				$orderField = " c.fantasia";
				break;
	
			case 'razaoSocial':
				$fields = " '' AS fantasia, 0 AS TIPO_ESTE, 'Todos' AS desc_tipo_est, ";
				$fields .= " c.CNPJ clicod, fn_busca_nfse(c.CNPJ, TO_DATE('".$dataInicial."', 'dd/mm/yyyy'), 
							TO_DATE('".$dataFinal."', 'dd/mm/yyyy')) AS caminho, min(c.email) as email";
	
				$groupFields = " c.nome, c.CNPJ";
				$orderField = " c.nome";
				break;
	
			case 'tipoEsterilizacao':
				$fields = " c.fantasia, n.TIPO_ESTE, te.descricao AS desc_tipo_est, ";
				$fields .= " n.clicod, fn_busca_nfse(n.clicod, TO_DATE('".$dataInicial."', 'dd/mm/yyyy'), 
							TO_DATE('".$dataFinal."', 'dd/mm/yyyy')) AS caminho, c.email";
	
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
	
			
			$sql = "select $fields
						  to_char(n.DATAESTE, 'dd/mm/yyyy') as dataeste,
						  sum(n.Qtd) qtd, 
						  to_char(sum(n.TOTALDesc),'99G999G990D99') as total, 
						  to_char(sum(n.transporte),'99G999G990D99') as transporte,  
						  to_char(sum(n.TOTALD),'99G999G990D99') as TOT_C_TRANSPORTE
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