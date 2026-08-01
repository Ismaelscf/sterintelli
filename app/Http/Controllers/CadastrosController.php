<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Repositories\CadastrosRepository;

class CadastrosController extends Controller
{
    
    public function __construct()
    {
      $this->repository = new CadastrosRepository();
    }


    public function indexClientes()
    {

        $msgInforma = $this->msgInforma;

    	$dados = $this->repository->listarClientes();
        return view('cadastros.clientes.index', compact('dados', 'msgInforma'));

    }

    public function preEditarCliente($tipo, Request $request)
    {
        $dados =[];

        $estados = $this->repository->consultarEstadosCod();
        $municipios = $this->repository->consultarMunicipiosCod();

        if ($tipo == 'A'){
            $idcliente = $request->id;
            $dados = $this->repository->detalharCliente($idcliente);
            $destino = 'cadastros.clientes.preeditarcliente';
        }else
            $destino = 'cadastros.clientes.preinserircliente';

        return view($destino, compact('tipo','dados', 'estados', 'municipios'));
    }

    public function posEditarCliente($tipo, Request $request)
    {


        $dados = array(
                        'NOME' =>               $request->NOME , 
                        'FANTASIA' =>           $request->FANTASIA , 
                        'ENDERECO' =>           $request->ENDERECO , 
                        'NUMERO' =>             $request->NUMERO , 
                        'BAIRRO' =>             $request->BAIRRO , 
                        'UF' =>                 $request->cmbEstado , 
                        'CEP' =>                $request->CEP , 
                        'TELEFONE' =>           $request->TELEFONE , 
                        'FAX' =>                $request->FAX , 
                        'EMAIL' =>              $request->EMAIL , 
                        'CNPJ' =>               $request->CNPJ , 
                        'IM' =>                 $request->IM , 
                        'IE' =>                 $request->IE , 
                        'DESCONTO' =>           $request->DESCONTO , 
                        'TAXA_TRANSPORTE' =>    $request->TAXA_TRANSPORTE , 
                        'BOL_ATIVO' =>          $request->BOL_ATIVO , 
                        'MSGNF' =>              $request->MSGNF , 
                        'ANOTACOES' =>          $request->ANOTACOES , 
                        'municipio_id' =>       $request->cmbMunicipio,
                );  

        if ($request->DATA_INATIVIDADE)
            $dados['DATA_INATIVIDADE'] = $request->DATA_INATIVIDADE;

        if ($tipo == 'I'){
            $retorno = $this->repository->insertCliente($dados);
            $msg = 'Cliente inserido com sucesso.';

        }else{
            $condicao = array('CODIGO' => $request->CODIGO); 
            $retorno = $this->repository->updateCliente($dados, $condicao);
            $msg = 'Cliente alterado com sucesso.';
        }
        if ($retorno ==  true){
            array_push($this->msgInforma, $msg);
            return $this->indexClientes();
        }else
            redirect()->back()->withErrors([$retorno]);
       
    }

    public function posDeletarCliente(Request $request)
    {
        $condicao = array('CODIGO' => $request->CODIGO); 
        $retorno = $this->repository->deleteCliente($condicao);
        $msg = 'Cliente excluído com sucesso.';
        
        if ($retorno ==  true){
            array_push($this->msgInforma, $msg);
            return $this->indexClientes();
        }else
            redirect()->back()->withErrors([$retorno]);
    }


}
