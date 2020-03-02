<?php

namespace App\Repositories;

use stdClass;

class BaseRepository
{
	private $conn;
	private $result;
    public $data;
    public $count;


    public function __construct()
    {
    	$this->conn = oci_connect('scott', 'tiger', env('DB_TNS', ''), 'AL32UTF8');
        if (!$this->conn) {
            $e = oci_error();
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        }
        $std = oci_parse($this->conn, "alter session set NLS_NUMERIC_CHARACTERS=',.'");
        oci_execute($std);

    }

    public function executaSql($sql){


        //echo '<br>'.$sql;
        $this->result = oci_parse($this->conn, $sql);
        if (!$this->result) {
            $e = oci_error($this->conn);
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        }

        // Perform the logic of the query
        $test = oci_execute($this->result);
        if (!$test) {
            $e = oci_error($this->result);
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        }

        if (strpos(strtoupper($sql), 'INSERT') === false) { 
            $this->count = oci_fetch_all($this->result, $this->data, null, null, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
            $this->data = json_decode(json_encode($this->data));
        }
        else
            $this->count = oci_num_rows($this->result);

         
    }

    public function __destruct() {
        /*
        if ($this->stid)
            oci_free_statement($this->stid);

        if ($this->conn)
            oci_close($this->conn);
            */ 
    }


    function consultarClientesCompleto($uf = '')
    {
        $sql = "select codigo cod, UPPER(REPLACE(fantasia,'''', ' ' )) nome, 
                        UPPER(REPLACE(nome,'''', ' ')) razao
                from clientes 
                where nome is not null ";

        if($uf  != ''){
            $sql .= " and UF = '$uf'";
        }

        $sql .= " and bol_ativo = 'S' order by fantasia";


        //echo $sql;
        $this->executaSQL($sql);
        return $this->data;


    }

    function consultarEstados()
    {
        $sql = "select TES_CODIGO cod, TES_DESCRICAO nome 
                from tab_estados 
                order by  TES_CODIGO";
        $this->executaSQL($sql);
        return $this->data;

    }   

    function consultarMunicipios($uf  = '')
    {
        $sql = "select distinct municipio cod, municipio nome
                from clientes";

        if($uf  != ''){
            $sql .= " where UF = '$uf'";
        }

        $sql .= " order by 1";

        $this->executaSQL($sql);
        return $this->data;


    }   
    function consultarMunicipiosCod($uf  = '')
    {
        $sql = " select cod_siafi, nome, uf, cod_ibge
                 from tab_municipio ";

        if($uf  != ''){
            $sql .= " where UF = '$uf'";
        }

        $sql .= " order by uf, nome";

        $this->executaSQL($sql);
        return $this->data;


    } 


    public function consultaFaturamento($dtIni, $dtFim, $idCliente){
        $sql = " SELECT  
                    c.nome, 
                    FANTASIA, 
                    n.cliente as clicod,
                    to_char(round(sum(TOTALD),2), 'FM999G999G999D90') totald, 
                    to_char(nvl(round(sum(TOTAL) * (n.DESCONTO/100),2), 0), 'FM999G999G999D90') DESCONTO,
                    to_char(round(sum(TRANSPORTE),2), 'FM999G999G999D90') transporte,
                    to_char(round(sum(TOTAL),2), 'FM999G999G999D90') total, 
                    to_char(round(sum(TOTALDESC),2), 'FM999G999G999D90') TOTALDESC
                FROM NOTA_TOTAL3 n
                inner join clientes c on n.cliente = c.codigo 
                where  DATAESTE BETWEEN to_date('".$dtIni."', 'dd/mm/yyyy') 
                       AND to_date('".$dtFim."', 'dd/mm/yyyy')
                and n.cliente = $idCliente
                group by CLIENTE, FANTASIA, n.cliente, nascimento, n.desconto, nome
                    order by fantasia";



        $this->executaSql($sql);
        return $this->data[0];
    }


    public function consultaFaturamentoItens($dtIni, $dtFim, $idCliente){
        $sql = " SELECT nome,  
                to_char(UNITARIO, 'FM999G999G999D90') val_unitario, 
                sum(QUANTIDADE) qtd, 
                to_char(sum(TOTAL), 'FM999G999G999D90') total
                from vie_itens_nota3
                where DATAESTE BETWEEN to_date('".$dtIni."', 'dd/mm/yyyy')  
                AND to_date('".$dtFim."', 'dd/mm/yyyy')  
                and CLIENTE = $idCliente
                group by 
                CLIENTE, SERVICO, UNITARIO, UNITARIOD, nome
                order by nome";

        $this->executaSql($sql);
        return $this->data;
    }    


}
