<?php
namespace App\Repositories;

class FaturamentoRepository extends BaseRepository
{


    function consultarFatPeriodo($dataInicial, $dataFinal, $cliente, $estado, $municipio)
        {
			

			$sql = "select c.fantasia, sum(n.qtd) qtd, 
							to_char(SUM(n.totaldesc),'99G999G990D99') as TOTAL,
							to_char(SUM(n.transporte),'99G999G990D99') as TRANSPORTE,
							to_char(SUM(n.totald),'99G999G990D99') as TOT_C_TRANSPORTE,
							n.clicod,
							fn_busca_nfse(n.clicod, to_date('".$dataInicial."', 'dd/mm/yyyy') , 
							to_date('".$dataFinal."', 'dd/mm/yyyy') ) as caminho
					from vie_nota_detalhe N inner join clientes c on n.clicod = c.codigo
					where DATAESTE BETWEEN to_date('".$dataInicial."', 'dd/mm/yyyy') AND to_date('".$dataFinal."', 'dd/mm/yyyy')";

			if ($cliente != "-1")
				$sql .= " and n.clicod = $cliente";

			if ($estado != "-1")
				$sql .=" and c.codigo = n.clicod and upper(c.UF) = '$estado'";

			if ($municipio != "-1")
				$sql .=" and c.codigo = n.clicod and upper(c.municipio) = '$municipio'";		

			$sql .= " GROUP BY c.fantasia,
							n.clicod 
					  order by c.fantasia";	

			
			//echo $sql;
			$this->executaSQL($sql);
			return $this->data;
		}


    function consultarDetFatPeriodo($dataInicial, $dataFinal, $cliente)
        {
			
			$sql = "select c.codigo, c.fantasia, c.nome, 
							to_char(n.DATAESTE, 'dd/mm/yyyy') as dataeste,
						  n.Qtd, 
						  to_char(n.TOTALDesc,'99G999G990D99') as total, 
						  to_char(n.transporte,'99G999G990D99') as transporte,  
						  to_char(n.TOTALD,'99G999G990D99') as TOT_C_TRANSPORTE
					from vie_nota_detalhe N inner join clientes c on n.clicod = c.codigo
					where DATAESTE BETWEEN to_date('".$dataInicial."', 'dd/mm/yyyy') AND to_date('".$dataFinal."', 'dd/mm/yyyy')
					 and n.clicod = $cliente
					  order by DATAESTE asc ";	

			//echo $sql;
			$this->executaSQL($sql);
			return $this->data;
		}


}