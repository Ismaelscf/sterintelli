<?php
namespace App\Repositories;

class CadastrosRepository extends CrudRepository
{

	function listarClientes()
    {

    	$sql = "SELECT codigo, fantasia, CNPJ, nome, desconto,
			            taxa_transporte as taxa,
			            endereco || ' - ' || bairro || ' - ' || municipio  || '(' || uf || ')' as dados
				FROM CLIENTES";
    	$dados = $this->listCrud($sql);

    	return $dados;
    }



}
