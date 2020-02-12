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
					where DATAESTE BETWEEN to_date('$dataInicial', 'dd/mm/yyyy') AND to_date('$dataFinal', 'dd/mm/yyyy') ";

			if ($cliente != "")
				$sql .= " and n.clicod = $cliente";

			if ($estado != "")
				$sql .=" and c.codigo = n.clicod and upper(c.UF) = '$estado'";

			if ($municipio != "")
				$sql .=" and c.codigo = n.clicod and upper(c.municipio) = '$municipio'";		

			$sql .= " GROUP BY c.fantasia,
							n.clicod 
					  order by c.fantasia";	

			
			$this->executaSQL($sql);
			eturn $this->data[0];
		}

}