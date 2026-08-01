<?php
namespace App\Repositories;

class CrudRepository extends BaseRepository
{


   // Atributo onde será guardado o nome da tabela    
   private $tabela = null;   
    
   // Atributo estático que contém uma instância da própria classe   
   private static $crud = null;   


   public function setTableName($tabela){   
       $this->tabela = $tabela; 
   }  


   private function buildInsert($arrayDados){   
    
       // Inicializa variáveis   
       $sql = "";   
       $campos = "";   
       $valores = "";   
              
       // Loop para montar a instrução com os campos e valores   
       foreach($arrayDados as $chave => $valor){   
          $campos .= $chave . ', ';   
          $valores .= '?, ';   
       }   
              
       // Retira vírgula do final da string   
       $campos = (substr($campos, -2) == ', ') ? trim(substr($campos, 0, (strlen($campos) - 2))) : $campos ;    
              
       // Retira vírgula do final da string   
       $valores = (substr($valores, -2) == ', ') ? trim(substr($valores, 0, (strlen($valores) - 2))) : $valores ;    
              
       // Concatena todas as variáveis e finaliza a instrução   
       $sql .= "INSERT INTO {$this->tabela} (" . $campos . ")VALUES(" . $valores . ")";   
              
       // Retorna string com instrução SQL   
       return trim($sql);   
   }   
    
   /*   
   * Método privado para construção da instrução SQL de UPDATE   
   * @param $arrayDados = Array de dados contendo colunas, operadores e valores   
   * @param $arrayCondicao = Array de dados contendo colunas e valores para condição WHERE   
   * @return String contendo instrução SQL   
   */    
   private function buildUpdate($arrayDados, $arrayCondicao){   
    
       // Inicializa variáveis   
       $sql = "";   
       $valCampos = "";   
       $valCondicao = "";   
              
       // Loop para montar a instrução com os campos e valores   
       foreach($arrayDados as $chave => $valor){   
          $valCampos .= $chave . '=?, ';   
       }   
              
       // Loop para montar a condição WHERE   
       foreach($arrayCondicao as $chave => $valor){   
          $valCondicao .= $chave . '? AND ';   
       }   
              
       // Retira vírgula do final da string   
       $valCampos = (substr($valCampos, -2) == ', ') ? trim(substr($valCampos, 0, (strlen($valCampos) - 2))) : $valCampos ;    
              
       // Retira vírgula do final da string   
       $valCondicao = (substr($valCondicao, -4) == 'AND ') ? trim(substr($valCondicao, 0, (strlen($valCondicao) - 4))) : $valCondicao ;    
              
        // Concatena todas as variáveis e finaliza a instrução   
        $sql .= "UPDATE {$this->tabela} SET " . $valCampos . " WHERE " . $valCondicao;   
              
        // Retorna string com instrução SQL   
        return trim($sql);   
   }   
    
 /*   
   * Método privado para construção da instrução SQL de DELETE   
   * @param $arrayCondicao = Array de dados contendo colunas, operadores e valores para condição WHERE   
   * @return String contendo instrução SQL   
   */    
   private function buildDelete($arrayCondicao){   
    
        // Inicializa variáveis   
        $sql = "";   
        $valCampos= "";   
              
        // Loop para montar a instrução com os campos e valores   
        foreach($arrayCondicao as $chave => $valor){   
           $valCampos .= $chave . '? AND ';   
        }   
              
        // Retira a palavra AND do final da string   
        $valCampos = (substr($valCampos, -4) == 'AND ') ? trim(substr($valCampos, 0, (strlen($valCampos) - 4))) : $valCampos ;    
              
        // Concatena todas as variáveis e finaliza a instrução   
        $sql .= "DELETE FROM {$this->tabela} WHERE " . $valCampos;   
              
        // Retorna string com instrução SQL   
        return trim($sql);   
   }   
    
   /*   
   * Método público para inserir os dados na tabela   
   * @param $arrayDados = Array de dados contendo colunas e valores   
   * @return Retorna resultado booleano da instrução SQL   
   */   
   public function insertCrud($arrayDados){   
      try {   
    
        // Atribui a instrução SQL construida no método   
        $sql = $this->buildInsert($arrayDados);   
    
        // Passa a instrução para o PDO   
        $stm = oci_parse($this->conn, $sql);   
    
        // Loop para passar os dados como parâmetro   
        $cont = 1;   
              foreach ($arrayDados as $valor){   
                    $stm->bindValue($cont, $valor);   
                    $cont++;   
              }   
    
        // Executa a instrução SQL e captura o retorno   
        $retorno = oci_execute($stm);   
    
        return $retorno;   
           
      } catch (Exception $e) {   
        echo "Erro: " . $e->getMessage();   
      }   
   }   
    
   /*   
   * Método público para atualizar os dados na tabela   
   * @param $arrayDados = Array de dados contendo colunas e valores   
   * @param $arrayCondicao = Array de dados contendo colunas e valores para condição WHERE - Exemplo array('$id='=>1)   
   * @return Retorna resultado booleano da instrução SQL   
   */   
   public function updateCrud($arrayDados, $arrayCondicao){   
      try {   
    
        // Atribui a instrução SQL construida no método   
        $sql = $this->buildUpdate($arrayDados, $arrayCondicao);   
    
        // Passa a instrução para o PDO   
        $stm = oci_parse($this->conn, $sql);   
    
        // Loop para passar os dados como parâmetro   
        $cont = 1;   
        foreach ($arrayDados as $valor){   
            $stm->bindValue($cont, $valor);   
            $cont++;   
        }   
              
        // Loop para passar os dados como parâmetro cláusula WHERE   
        foreach ($arrayCondicao as $valor){   
            $stm->bindValue($cont, $valor);   
            $cont++;   
        }   
    
        // Executa a instrução SQL e captura o retorno   
        $retorno = oci_execute($stm);  
    
        return $retorno;   
           
      } catch (Exception $e) {   
        echo "Erro: " . $e->getMessage();   
      }   
   }   
    
   /*   
   * Método público para excluir os dados na tabela   
   * @param $arrayCondicao = Array de dados contendo colunas e valores para condição WHERE - Exemplo array('$id='=>1)   
   * @return Retorna resultado booleano da instrução SQL   
   */   
   public function deleteCrud($arrayCondicao){   
      try {   
    
        // Atribui a instrução SQL construida no método   
        $sql = $this->buildDelete($arrayCondicao);   
    
        // Passa a instrução para o PDO   
        $stm = oci_parse($this->conn, $sql);   
    
              // Loop para passar os dados como parâmetro cláusula WHERE   
	      $cont = 1;   
	      foreach ($arrayCondicao as $valor){   
	        $stm->bindValue($cont, $valor);   
	        $cont++;   
	      }   
    
        // Executa a instrução SQL e captura o retorno   
        $retorno = oci_execute($stm); 
    
        return $retorno;   
           
      } catch (PDOException $e) {   
        echo "Erro: " . $e->getMessage();   
      }   
   }   
  

	/*  
   * Método lista
   * @param $sql = Instrução SQL inteira contendo, nome das tabelas envolvidas, JOINS, WHERE, ORDER BY, GROUP BY e LIMIT  
   * @param $arrayParam = Array contendo somente os parâmetros necessários para clásusla WHERE  
   * @param $fetchAll  = Valor booleano com valor default TRUE indicando que serão retornadas várias linhas, FALSE retorna apenas a primeira linha  
   * @return Retorna array de dados da consulta em forma de objetos  
   */  
   public function listCrud($sql, $arrayParams=null, $fetchAll=TRUE){  
      try {   
    
        // Passa a instrução para o PDO   
        $stm = oci_parse($this->conn, $sql);   
    
        // Verifica se existem condições para carregar os parâmetros    
        if (!empty($arrayParams)){
    
          // Loop para passar os dados como parâmetro cláusula WHERE   
          $cont = 1;   
          foreach ($arrayParams as $valor){   
            $stm->bindValue($cont, $valor);   
            $cont++;   
          }   
        
        }   
    
        // Executa a instrução SQL    
        $retorno = oci_execute($stm);    

        // Verifica se é necessário retornar várias linhas  
        $count = oci_fetch_all($stm, $dados, null, null, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
        $dados = json_decode(json_encode($dados));
    
        return $dados;   
           
      } catch (Exception $e) {   
        echo "Erro: " . $e->getMessage();   
      } 
   } 

}

	