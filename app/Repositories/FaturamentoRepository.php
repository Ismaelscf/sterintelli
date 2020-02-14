<?php
namespace App\Repositories;

class FaturamentoRepository extends BaseRepository
{


    function consultarFatPeriodo($dataInicial, $dataFinal, $cliente, $estado, $municipio)
        {
			

			$sql = "select c.fantasia, sum(n.qtd) qtd, 
							to_char(SUM(n.totaldesc),'99G999G999D99') as TOTAL,
							to_char(SUM(n.totald),'99G999G999D99') as TOT_C_TRANSPORTE,
							n.clicod
					from vie_nota_detalhe N inner join clientes c on n.clicod = c.codigo
					where DATAESTE BETWEEN to_date('$dataInicial', 'dd/mm/yyyy') AND to_date('$dataFinal', 'dd/mm/yyyy')";

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
			//var_dump($this->data[0]);
			return $this->data;
		}



}