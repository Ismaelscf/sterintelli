<?php
namespace App\Repositories;



class AuthRepository extends BaseRepository
{

	 public $usuario;

	 function consultaAutentica($login, $senha)
    {       	
		//CODIGO, NOME, LOGINNAME, SENHA, DIREITO, LOGINACESSO
		

	    if($this->verificaExistencia("tab_usuario", 
	    						"UPPER(LOGINNAME) = UPPER('$login') 
	    						and SENHA = '$senha' 
	    						and DIREITO IN (8,9,11,12,13,10)") == true){
			
			$sql = "SELECT CODIGO, NOME, LOGINNAME, DIREITO 
					 FROM tab_usuario 
					 where UPPER(LOGINNAME) = UPPER('$login') 
					 and SENHA = '$senha' 
					 and DIREITO IN (8,9,11,12,13,10)";
			
			$this->executaSql($sql);

			if($this->count > 0){
				$this->usuario = $this->data[0];
				return true;
			}else
				return false;
								
		}

    }

    function verificaExistencia($tabela, $condicao){
	
		$sql = "select count(*) qtd from $tabela";
		if(!empty($condicao))
			$sql.= " where $condicao";

		$this->executaSql($sql);

		return $this->data[0]->QTD > 0? true:false;
	}	
	
}