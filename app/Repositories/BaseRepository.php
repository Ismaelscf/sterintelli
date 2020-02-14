<?php

namespace App\Repositories;

class BaseRepository
{
	private $conn;
	private $result;
    public $data;
    public $count;


    public function __construct()
    {
    	$this->conn = oci_connect('scott', 'tiger', 'XE');
        if (!$this->conn) {
            $e = oci_error();
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        }
        $std = oci_parse($this->conn, "alter session set NLS_NUMERIC_CHARACTERS=',.'");
        oci_execute($std);
    }

    public function executaSql($sql){


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

         $this->count = oci_fetch_all($this->result, $this->data, null, null, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);

         $this->data = json_decode(json_encode($this->data));
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


}
