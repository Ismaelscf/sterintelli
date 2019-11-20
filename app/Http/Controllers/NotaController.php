<?php

namespace App\Http\Controllers;

use App\Nota;
use Illuminate\Http\Request;
use NFePHP\Common\Certificate;
use NFePHP\NFSeDSF\Tools;
use NFePHP\NFSeDSF\Rps;
use NFePHP\NFSeDSF\Common\Soap\SoapCurl;
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
   
        return redirect()->route('notas.index')
                        ->with('success','Nota criada com sucesso.');
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
