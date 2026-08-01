<?php

namespace App\Repositories;

use stdClass;

class BaseRepository
{
	protected $conn;
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
        $std = oci_execute($this->result);
        if (!$std) {
            $e = oci_error($this->result);
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        }

        $sql = strtoupper($sql);
        if (strpos($sql, 'INSERT') === false && strpos($sql, 'UPDATE') === false && strpos($sql, 'DELETE') === false) { 
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
        $sql = "select codigo cod, UPPER(REPLACE(fantasia,'''', ' ' )) fantasia, 
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
        $sql = " select to_char(cod_siafi, '000000') cod_siafi, 
                 nome, uf, cod_ibge
                 from tab_municipio ";

        if($uf  != ''){
            $sql .= " where UF = '$uf'";
        }

        $sql .= " order by uf, nome";

        $this->executaSQL($sql);
        return $this->data;


    } 

    function consultarEstadosCod($uf  = '')
    {
        $sql = " select distinct uf
                 from tab_municipio 
                 order by uf";

        $this->executaSQL($sql);
        return $this->data;


    }     


    public function consultaFaturamento($dtIni, $dtFim, $idCliente, $searchOption, $tipoEste= 0){


        $sqlJoin = "";
        $sqlGroup = " GROUP BY n.desconto, ";
        $sqlOrder = " ORDER BY ";

        switch ($searchOption) {
            case 'fantasia':
                $fields = " c.nome, FANTASIA, n.cliente as clicod, ";

                $sqlWhere = " and   n.cliente = $idCliente";

                $sqlGroup .= " c.nome, c.fantasia, n.cliente";
                $sqlOrder .= " c.fantasia";
                break;

            case 'razaoSocial':
                $fields = " c.nome, '' FANTASIA, c.CNPJ as clicod,";
                            
                $sqlWhere = " and   c.CNPJ = '$idCliente'";
                $sqlGroup .= " c.nome, c.CNPJ";
                $sqlOrder .= " c.nome";
                break;

            case 'tipoEsterilizacao':
                $fields = " c.nome, c.fantasia, ";

                $sqlWhere = " and   n.cliente = $idCliente";
                if($tipoEste > 0)
                    $sqlWhere .= " and  n.TIPO_EST = $tipoEste";

                $sqlGroup .= " c.nome, c.fantasia, n.cliente";
                $sqlOrder .= " c.fantasia";
                break;
            }



        $sql = " SELECT $fields
                        to_char(round(sum(TOTALD),2), 'FM999G999G999D90') totald, 
                        to_char(nvl(round(sum(TOTAL) * (n.DESCONTO/100),2), 0), 'FM999G999G999D90') DESCONTO,
                        to_char(round(sum(TRANSPORTE),2), 'FM999G999G999D90') transporte,
                        to_char(round(sum(TOTAL),2), 'FM999G999G999D90') total, 
                        to_char(round(sum(TOTALDESC),2), 'FM999G999G999D90') TOTALDESC
                FROM NOTA_TOTAL3 n
                inner join clientes c on n.cliente = c.codigo 
                $sqlJoin 
                where  DATAESTE BETWEEN to_date('".$dtIni."', 'dd/mm/yyyy') 
                       AND to_date('".$dtFim."', 'dd/mm/yyyy')
                $sqlWhere
                $sqlGroup
                $sqlOrder ";


        //print($sql);die;
;       $this->executaSql($sql);
        return $this->data[0];
    }


    public function consultaFaturamentoItens($dtIni, $dtFim, $idCliente, $searchOption, $tipoEste= 0){



        $sqlJoin = "";
        $sqlGroup = " GROUP BY n.desconto, ";
        $sqlOrder = " ORDER BY ";

        switch ($searchOption) {
            case 'fantasia':
                
                $sqlWhere = " and  cliente = $idCliente";

                $sqlGroup .= " c.nome, c.fantasia, n.cliente";
                $sqlOrder .= " c.fantasia";
                break;

            case 'razaoSocial':
                $sqlWhere = " and   c.CNPJ = '$idCliente'";

                $sqlGroup .= " c.nome, c.CNPJ";
                $sqlOrder .= " c.nome";
                break;

            case 'tipoEsterilizacao':
                $fields = " c.nome, c.fantasia ";

                $sqlWhere = " and   cliente = $idCliente";
                /*if($tipoEste > 0)
                    $sqlWhere = " and  n.TIPO_ESTE = $tipoEste";

                $sqlGroup .= " c.nome, c.fantasia, n.cliente";*/
                $sqlOrder .= " c.fantasia";
                break;
            }



        $sql = " SELECT vn.nome,  
                to_char(UNITARIO, 'FM999G999G999D90') val_unitario, 
                sum(QUANTIDADE) qtd, 
                to_char(sum(TOTAL), 'FM999G999G999D90') total
                from vie_itens_nota3 vn
                inner join clientes c on cliente = c.codigo
                where DATAESTE BETWEEN to_date('".$dtIni."', 'dd/mm/yyyy')  
                AND to_date('".$dtFim."', 'dd/mm/yyyy')  
                $sqlWhere
                group by 
                UNITARIO, UNITARIOD, vn.nome
                order by nome";

        //print($sql);die;
        $this->executaSql($sql);
        return $this->data;
    }  




}
