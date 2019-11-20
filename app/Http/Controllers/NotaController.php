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

        //$content = file_get_contents('C:\dev_stef\certificado\expired_certificate.pfx');
        //$content = file_get_contents('C:\dev_stef\certificado\STEFERSON_20191105.p12');
        $password = 'associacao';
        //$this->cert = Certificate::readPfx($content, $password);
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
        return view('notas.emitir');
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



        try {
            
            $tools = new Tools($this->configJson, $this->cert);
 
            $arps = [];
            


            $std = new \stdClass();
            $std->inscricaomunicipalprestador = '10517900';
            $std->razaosocialprestador = 'EMPRESA MODELO';
            $std->tiporps = 'RPS';
            $std->serierps = 'NF';
            $std->numerorps = 84;
            $std->dataemissaorps = '2009-11-21T15:30:00';
            $std->situacaorps = 'N';
            //$std->serierpssubstituido = '';
            //$std->numerorpssubstituido = '0';
            //$std->numeronfsesubstituida = '0';
            //$std->dataemissaonfsesubstituida = '1900-01-01';
            $std->serieprestacao = '99';
            $std->inscricaomunicipaltomador = '0000000';
            $std->cpfcnpjtomador = '00000000191';
            $std->razaosocialtomador = 'EMPRESA DE TESTES';
            $std->tipologradourotomador = 'Rua';
            $std->logradourotomador = 'SETE DE SETEMBRO';
            $std->numeroenderecotomador = '335';
            $std->complementoenderecotomador = '';
            $std->tipobairrotomador = 'Bairro';
            $std->bairrotomador = 'Centro';
            $std->cidadetomador = '0001219';
            $std->cidadetomadordescricao = 'TERESINA';
            $std->ceptomador = '64001210';
            $std->emailtomador = 'res@bol.com.br';
            $std->codigoatividade = '412040000';
            $std->aliquotaatividade = 5.00;
            $std->tiporecolhimento = 'A';
            $std->municipioprestacao = '0001219';
            $std->municipioprestacaodescricao = 'TERESINA';
            $std->operacao = 'A';
            $std->tributacao = 'T';
            $std->valorpis = 0.00;
            $std->valorcofins = 0.00;
            $std->valorinss = 0.00;
            $std->valorir = 0.00;
            $std->valorcsll = 0.00;
            $std->aliquotapis = 0.0000;
            $std->aliquotacofins = 0.0000;
            $std->aliquotainss = 0.0000;
            $std->aliquotair = 0.0000;
            $std->aliquotacsll = 0.0000;
            $std->descricaorps = "MES/ANO DE REFERENCIA DA PRESTACAO DE SERVICO:12-2009 .VENCIMENTO =08/01/2010 VALOR LIQUIDO A PAGAR  R$3669,38SERVICO DE PORTARIA -RPS enviado em teste";

            $std->itens[0] = new stdClass();
            $std->itens[0]->discriminacaoservico = "Descricao do Servico ...";
            $std->itens[0]->quantidade = 1;
            $std->itens[0]->valorunitario = 100.0000;
            $std->itens[0]->valortotal = 100.00;
            $std->itens[0]->tributavel = 'S';

            $rps = new Rps($std);
            
            $arps[] = $rps;    
            $lote = '123456';
           
            $response = $tools->enviarSincrono($arps, $lote);

            //echo FakePretty::prettyPrint($response, '');


        } catch (\Exception $e) {
            echo $e->getMessage();
        }
   
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
