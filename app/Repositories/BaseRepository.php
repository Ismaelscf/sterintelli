<?php

namespace App\Repositories;

class BaseRepository
{
	private $conn;
	public $result;
    public $data;


    public function __construct()
    {
    	$this->conn = oci_connect('scott', 'tiger', 'XE');
        if (!$this->conn) {
            $e = oci_error();
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        }
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

         oci_fetch_all($this->result, $this->data, null, null, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
    }

    public function __destruct() {
        
        /*if ($this->stid)
            oci_free_statement($this->stid);

        if ($this->conn)
            oci_close($this->conn);*/
    }



}
