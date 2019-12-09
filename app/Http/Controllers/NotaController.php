<?php

namespace App\Http\Controllers;

use App\Nota;
use Illuminate\Http\Request;
use NFePHP\Common\Certificate;
use NFePHP\NFSeDSF\Rps;
use NFePHP\NFSeDSF\Common\Soap\SoapCurl;
use NFePHP\NFSeDSF\Tools;
use stdClass;

class NotaController extends Controller
{
    
    protected $config;
    protected $configJson;
    protected $cert;


    public function __construct(stdClass $rps)
    {
       $this->config = [
                'cnpj' => '01469892000137',
                'im' => '7048009',
                'cmun' => '2111300', //ira determinar as urls e outros dados
                'razao' => 'BRITO e SOARES LTDA',
                'tpamb' => 2, //1-producao, 2-homologacao
                'token' => '3579F09B4CC37151D3327197B13F9583',
            ];


        $this->configJson = json_encode($this->config);

        
        $content = file_get_contents('C:\dev_stef\certificado\expired_certificate.pfx');
        //$content = file_get_contents('C:\dev_stef\certificado\STEFERSON_20191105.p12');
        $password = 'associacao';
        $this->cert = Certificate::readPfx($content, $password);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
     
        $notas = Nota::latest()->paginate(5);
  
        return view('notas.index', compact('notas'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function emitir()
    {
        $cnpjprestador = '01469892000137';
        $inscricaomunicipalprestador = '7048009';
        $razaosocialprestador = 'BRITO e SOARES LTDA';
        $token = '3579F09B4CC37151D3327197B13F9583';

        return view('notas.pre-emitir', compact('cnpjprestador', 'inscricaomunicipalprestador',
            'razaosocialprestador', 'token'));  

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function enviar(Request $request)
    {
  
        //Nota::create($request->all());



        //try {
            
            $tools = new Tools($this->configJson, $this->cert);
 
            $arps = [];
            
            $std = new \stdClass();
            $std->inscricaomunicipalprestador = $request->has('inscricaomunicipalprestador')? $request->inscricaomunicipalprestador : "";
            $std->razaosocialprestador = $request->has('razaosocialprestador')? $request->razaosocialprestador : "";

            //dados do RPS
            $std->tiporps = 'RPS';
            $std->serierps = 'NF';
            $std->numerorps = 90;
            $std->dataemissaorps = '2009-11-21T15:30:00';
            $std->situacaorps = 'N';
            $std->serieprestacao = '99';

            //DADOS DO TOMADOR
            $std->inscricaomunicipaltomador = $request->has('inscricaomunicipaltomador')? $request->inscricaomunicipaltomador : "123";
            $std->cpfcnpjtomador = $request->has('cpfcnpjtomador')? $request->cpfcnpjtomador : "0000000";
            $std->razaosocialtomador = $request->has('razaosocialtomador')? $request->razaosocialtomador : "";
            $std->tipologradourotomador = $request->has('tipologradourotomador')? $request->tipologradourotomador : "";
            $std->logradourotomador = $request->has('logradourotomador')? $request->logradourotomador : "";
            $std->numeroenderecotomador = $request->has('numeroenderecotomador')? $request->numeroenderecotomador : "";
            $std->complementoenderecotomador = $request->has('complementoenderecotomador')? $request->complementoenderecotomador : "";
            $std->tipobairrotomador = $request->has('tipobairrotomador')? $request->tipobairrotomador : "";
            $std->bairrotomador = $request->has('bairrotomador')? $request->bairrotomador : "";
            $std->cidadetomador = $request->has('cidadetomador')? $request->cidadetomador : "";
            $std->cidadetomadordescricao = $request->has('cidadetomadordescricao')? $request->cidadetomadordescricao : "";
            $std->ceptomador = $request->has('ceptomador')? $request->ceptomador : "";
            $std->emailtomador = $request->has('emailtomador')? $request->emailtomador : "";


            $std->codigoatividade = $request->has('codigoatividade')? $request->codigoatividade : "";
            $std->codigoservico  = $request->has('codigoservico')? $request->codigoservico : "";
            $std->aliquotaatividade =$request->has('aliquotaatividade')? $request->aliquotaatividade : "";
            $std->tiporecolhimento = $request->has('tiporecolhimento')? $request->tiporecolhimento : "";
            $std->municipioprestacao = $request->has('municipioprestacao')? $request->municipioprestacao : "";
            $std->municipioprestacaodescricao = $request->has('municipioprestacaodescricao')? $request->municipioprestacaodescricao : "";
            $std->operacao = $request->has('operacao')? $request->operacao : "";
            $std->tributacao = $request->has('tributacao')? $request->tributacao : "";
            $std->valorpis = $request->has('valorpis')? $request->valorpis : 0.00;
            $std->valorcofins =$request->has('valorcofins')? $request->valorcofins : 0.00;
            $std->valorinss =$request->has('valorinss')? $request->valorinss : 0.00;
            $std->valorir =$request->has('valorir')? $request->valorir : 0.00;
            $std->valorcsll = $request->has('aliquotapis')? $request->aliquotapis : 0.00;
            $std->aliquotapis = $request->has('aliquotapis')? $request->aliquotapis : 0.00;
            $std->aliquotacofins = $request->has('aliquotacofins')? $request->aliquotacofins : 0.00;
            $std->aliquotainss = $request->has('aliquotainss')? $request->aliquotainss : 0.00;
            $std->aliquotair = $request->has('aliquotair')? $request->aliquotair : 0.00;
            $std->aliquotacsll = $request->has('aliquotacsll')? $request->aliquotacsll :0.00;
            $std->descricaorps = $request->has('descricaorps')? $request->descricaorps : "";

            $std->itens[0] = new stdClass();
            $std->itens[0]->discriminacaoservico = $request->has('descricaorps')? $request->descricaorps : "";
            $std->itens[0]->quantidade = $request->has('quantidade')? $request->quantidade : 1;
            $std->itens[0]->valorunitario = $request->has('valorunitario')? $request->valorunitario : 0.00;
            $std->itens[0]->valortotal = $request->has('valortotal')? $request->valortotal : 0.00;
            $std->itens[0]->tributavel = $request->has('tributavel')? $request->tributavel : "S";;


            $rps = new Rps($std);

            $arps[] = $rps;    
            $lote = '123456';
           
            $response = $tools->enviarSincrono($arps, $lote);

            //echo FakePretty::prettyPrint($response, '');


       /* } catch (\Exception $e) {
            echo $e->getMessage();
        }*/
   
        return  $response;//redirect()->route('notas.index')
                        //->with('success','Nota criada com sucesso.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\nota  $nota
     * @return \Illuminate\Http\Response
     */
    public function show(Nota $nota)
    {
        return view('notas.show',compact('nota'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\nota  $nota
     * @return \Illuminate\Http\Response
     */
    public function edit(Nota $nota)
    {
        return view('notas.edit',compact('nota'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\nota  $nota
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Nota $nota)
    {
         $request->validate([
            'numero' => 'required',
            'cnpjtomador' => 'required',
        ]);
  
        $nota->update($request->all());
   
        return redirect()->route('notas.index')
                        ->with('success','Nota atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\nota  $nota
     * @return \Illuminate\Http\Response
     */
    public function destroy(nota $nota)
    {
        $nota->delete();
  
        return redirect()->route('notas.index')
                        ->with('success','Nota excluida.');
    }
}
