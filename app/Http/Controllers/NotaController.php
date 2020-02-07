<?php
namespace App\Http\Controllers;

use App\Nota;
use App\Report;
use App\Config;
use Illuminate\Http\Request;
use NFePHP\Common\Certificate;
use NFePHP\NFSeDSF\Rps;
use NFePHP\NFSeDSF\Common\Soap\SoapCurl;
use NFePHP\NFSeDSF\Tools;
use stdClass;
use DOMDocument;

use App\Repositories\NotaRepository;

class NotaController extends Controller
{
    
    protected $config;
    protected $configJson;
    protected $cert;
    protected $repository;


    public function __construct(stdClass $rps)
    {
      $this->repository = new NotaRepository();

      //homolog
      //$token = '3579F09B4CC37151D3327197B13F9583';
      

      //prod
      $token = 'F0D5E8217DC2050AE0028EC24B2D70FE';
      $this->config = [
                'cnpj' => '01469892000137',
                'im' => '7048009',
                'cmun' => '2111300', //ira determinar as urls e outros dados
                'razao' => 'BRITO e SOARES LTDA',
                'tpamb' => 1, //1-producao, 2-homologacao
                'token' => '2734DB04D2D26922454C5107A750B4FC',
              ];



        $this->configJson = json_encode($this->config);

        
        $content = file_get_contents('C:\dev_stef\certificado\BRITOSORES2020.pfx');
        //$content = file_get_contents('/Users/steferson_1/dev_stef/certificado/BRITOSORES2020.pfx');
        //$content = file_get_contents('C:\dev_stef\certificado\STEFERSON_20191105.p12');
        $password = 'brito2020s';
        $this->cert = Certificate::readPfx($content, $password);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->repository->buscaNotas();   

        $notas = $this->repository->data;

        return view('notas.index', compact('notas'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function emitir($idcliente, Request $request)
    {
        $cnpjprestador = '01469892000137';
        $inscricaomunicipalprestador = '7048009';
        $razaosocialprestador = 'BRITO e SOARES LTDA';
        $token = '3579F09B4CC37151D3327197B13F9583';
        $dtInicial = date('Y-m-d H:i:s');
        $dtFinal = date('Y-m-d H:i:s');

        $dtIni = $request->query->has('dtini') ? $request->query->all()['dtini'] : null;
        $dtFim = $request->query->has('dtfim') ? $request->query->all()['dtfim'] : null;

        /*buscar os dados do período*/
        $dadosEmissao = $this->repository->buscaDadosEmissao($idcliente, $dtIni, $dtFim);   

        return view('notas.pre-emitir', compact('dadosEmissao'));  

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function enviar(Request $request)
    {
  
        //Nota::beginTransaction();

        //Nota::create($request->all());

        //try {

            $soap = new SoapCurl();
            $soap->disableCertValidation(true);
            
            $tools = new Tools($this->configJson, $this->cert);
            $soap->timeout(120);
            $tools->loadSoapClass($soap);


 
            $arps = [];
            
            $std = new \stdClass();
            $std->dtInicial = $request->has('dtInicial')? $request->dtInicial : "";
            $std->dtFinal = $request->has('dtFinal')? $request->dtFinal : "";
            $std->qtdade = $request->has('qtdade')? $request->qtdade : 1;
            $std->vTotServ = $request->has('vTotServ')? $request->vTotServ : 0.00;
            $std->vTotDeduc = $request->has('vTotDeduc')? $request->vTotDeduc : 0.00;


            $std->inscricaomunicipalprestador = $request->has('inscricaomunicipalprestador')? $request->inscricaomunicipalprestador : "";
            $std->razaosocialprestador = $request->has('razaosocialprestador')? $request->razaosocialprestador : "";

            //dados do RPS
            $std->tiporps = $request->has('tiporps')? $request->tiporps : "RPS";
            $std->serierps = $request->has('serierps')? $request->serierps : "NF";
            $std->numerorps = $request->has('numerorps')? $request->numerorps : 1;
            $std->dataemissaorps = $request->has('dataemissaorps')? $request->dataemissaorps :'2019-12-01T15:30:00';
            $std->situacaorps = $request->has('situacaorps')? $request->situacaorps :'N';
            $std->serieprestacao = $request->has('serieprestacao')? $request->serieprestacao :'99';

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

            $std->codigoatividade = $request->has('codigoatividade')? $request->codigoatividade : 0;
            $std->codigoservico  = $request->has('codigoservico')? $request->codigoservico : 0;
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
            $std->itens[0]->tributavel = $request->has('tributavel')? $request->tributavel : "S";


            $rps = new Rps($std);

            $arps[] = $rps;    
            $lote = '123456';
           
            $response = $tools->enviar($arps, $lote);


       // } catch (\Exception $e) {
            //Nota::rollBack();
           // echo $e->getMessage();
        //}
   
        return  $response;//redirect()->route('notas.index')
                        //->with('success','Nota criada com sucesso.');
    }

    public function preConsultarNotas()
    {
        return view('notas.pre-consultarnota');
    }


    public function consultarNotas(Request $request)
    {

        $tools = new Tools($this->configJson, $this->cert);

        $dtIni = $request->has('dtIni')? $request->dtIni : '2019-12-01';
        $dtFim = $request->has('dtFim')? $request->dtFim : '2019-12-31';

        $response = $tools->consultarNota($dtIni, $dtFim);

        $retorno = $this->trataRetorno($response);

        return  $retorno;
    }

    public function preConsultarNfse()
    {
        return view('notas.pre-consultarnfse');
    }

   public function consultarNfse(Request $request)
    {

        $notas[0] = ['numero' => 20104, 'codigo' => '3CC9.8C58.1F7F.4D56.EC16.9B46.4165.292F'];

        $soap = new SoapCurl();
        //$soap->disableCertValidation(true);

        $tools = new Tools($this->configJson, $this->cert);
        $soap->timeout(120);
        $tools->loadSoapClass($soap);

      
        $response = $tools->consultarNFSeRps($notas);

        //$response = $tools->consultarNFSeRps();

        return  $response;
        //return view('notas.consultanotas',compact('nota'));
    }

    public function cancelarNota(Request $request)
    {

        

        $soap = new SoapCurl();
        //$soap->disableCertValidation(true);

        $tools = new Tools($this->configJson, $this->cert);
        $soap->timeout(120);
        $tools->loadSoapClass($soap);

        $numero = '20473';
        $motivo = 'ERRO DE EMISSÃO - DESCRICAO DO SERVICO ERRADA';
        $codigoverificacao = '05E651C13A2AAF0C836C32C38BBE71BB';
    
        $response = $tools->cancelar($numero, $motivo, $codigoverificacao);

        //$response = $tools->consultarNFSeRps();

        return  $response;
        //return view('notas.consultanotas',compact('nota'));
    }


   public function consultarSeqRps(Request $request)
    {

        $soap = new SoapCurl();
        //$soap->disableCertValidation(true);

        $tools = new Tools($this->configJson, $this->cert);
        $soap->timeout(120);
        $tools->loadSoapClass($soap);

      
        $response = $tools->consultarSequencialRps();
        //$response = $tools->consultarNFSeRps();

        return  $response;
        //return view('notas.onsultanotas',compact('nota'));
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

    public function  danfse (nota $nota){//, $target) {

       //var_dump($nota);

        /*

  $nota = pdoGet('nfse', $notaId);
  $fatura = pdoGet('fatura', $nota->fatura_id);
  $filial = pdoGet('filial', $fatura->filial_id);

  $emitente = pdoGet('parceiro', $filial->parceiro_id);
  $emitMun = pdoGet('municipio', $emitente->municipio_id);
  $emitUF = pdoGet('uf', $emitMun->uf_id);

  $tomador = pdoGet('parceiro', $fatura->parceiro_id);
  $tomadorMun = pdoGet('municipio', $tomador->municipio_id);
  $tomadorUF = pdoGet('uf', $tomadorMun->uf_id);

*/

  $pdf = new Report(new Config('NFSe'));
  $pdf->SetMargins(10.5, 3, 20);
  $pdf->setPrintHeader(false);
  $pdf->setPrintFooter(false);
  $pdf->SetAutoPageBreak(False, 0);
  $pdf->AddPage();
  $pdf->SetCellPaddings(0, 0, 0, 0);
  $pdf->SetLineStyle(array('width' => 0.3));
  
  //--------------------------------------
  // Recibo
  //--------------------------------------
  /*$y = $pdf->GetY();
  $pdf->Box(135, '',
    "Recebemos de $nota->razaosocialprestador os serviços \nconstantes da NOTA FISCAL DE SERVIÇOS ELETRÔNICA indicada ao lado",
    0, 'TBR', 'L', 0, ['helvetica', '', 7], 'T'
  );
  $col2 = $pdf->GetX();
  $pdf->Ln();
  $pdf->Box(35, 'Data de recebimento', '', 0, 'BR', 'L', 9);
  $pdf->Box(100, 'Identificação e assinatura do recebedor', '', 1, 'BR', 'L', 9);
  $y1 = $pdf->GetY();
  $pdf->SetY($y);
  $pdf->SetX($col2);
  $pdf->Box(0, '', "NFS-e\nNº " . sprintf('%06d', $nota->numerorps), 1, 'TB', 'C', $y1-$y, ['helvetica', 'B', 10], 'M');
  $pdf->Ln(10);*/

  //--------------------------------------
  // Cabeçalho
  //--------------------------------------
  $y = $pdf->GetY();
  $pdf->Image( 'images/logo_slz.png', 11.5, $y+3, 18, 25);
  $pdf->Box(114.5, '', '', 0, 'TLBR', 'C', 32);
  $col2 = $pdf->GetX();
  $pdf->SetFont('helvetica', 'B', 13);
  $pdf->Text(48, $y+3, 'PREFEITURA DE SÃO LUÍS', false, false, true, 0, 1);
  $pdf->SetFont('helvetica', 'B', 9.8);
  $pdf->Text(44.5, $pdf->GetY()+4, 'SECRETARIA MUNICIPAL DE FAZENDA', false, false, true, 0, 1);
  $pdf->SetFont('helvetica', 'B', 9.9);
  $pdf->Text(35.5, $pdf->GetY()+4, 'NOTA FISCAL DE SERVIÇOS ELETRÔNICA - NFSe', false, false, true, 0, 1);
  
  
  $y1 = $pdf->GetY();
  $pdf->SetY($y);
  $pdf->SetX($col2);
  $pdf->Box(75, 'Número da Nota', sprintf('%06d', $nota->numerorps), 1, 'TBR', 'L', 10, ['helvetica', 'B', 10], 'B', false, ['helvetica', '', 9]);
  $pdf->SetX($col2);
  $pdf->Box(75, 'Data e Hora da Emissão', '18/09/2019 17:39:00', 1, 'TBR', 'L', 10, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 9]);
  $pdf->SetX($col2);
  $pdf->Box(75, 'Código de Verificação', '3DEA.A950.BB27.7D10.A770.55B1.219B.7E32', 1, 'TBR', 'L', 12, ['helvetica', 'B', 5], 'B', false, ['helvetica', '', 9]);

  //--------------------------------------
  // Grupos
  //--------------------------------------
  $pdf->fontCaption = ['helvetica', 'B', 6];
  $pdf->fontText = ['helvetica', '', 8];

  $yP = $pdf->GetY();
  $pdf->Box(189.5, '', "PRESTADOR DE SERVIÇOS", 1, 'BLR', 'C', 22, ['helvetica', 'B', 9], 'T');
  $yT = $pdf->GetY();
  $pdf->Image( 'images/logo_1.png', 12, $yP+3, 18, 18);

  $pdf->Box(189.5, '', "TOMADOR DE SERVIÇOS", 1, 'BLR', 'C', 22, ['helvetica', 'B', 9], 'T');
  $yD = $pdf->GetY();
  $pdf->Box(189.5, '', "DISCRIMINAÇÃO DOS SERVIÇOS", 1, 'BLR', 'C', 26, ['helvetica', 'B', 9], 'T');


  //Titulo Itens
  //---------------
  $pdf->Box(21, '', "Tipo do Item", 0, 'BLR', 'L', 5, ['helvetica', 'B', 7], 'T');
  $pdf->SetX(31.5);
  $pdf->Box(100, '', "Item", 0, 'BLR', 'L', 5, ['helvetica', 'B', 7], 'T');
  $pdf->SetX(131.5);
  $pdf->Box(21, '', "Quantidade", 0, 'BLR', 'R', 5, ['helvetica', 'B', 7], 'T');
  $pdf->SetX(152.5);
  $pdf->Box(21, '', "Valor Unitário (R$)", 0, 'BLR', 'R', 5, ['helvetica', 'B', 7], 'T');
  $pdf->SetX(173.5);
  $pdf->Box(26.5, '', "Valor Total (R$)", 1, 'BLR', 'R', 5, ['helvetica', 'B', 7], 'T');


  // Itens
  //---------------
  $pdf->Box(21, '', "TRIBUTÁVEL", 0, 'BLR', 'L', 85, ['helvetica', '', 6], 'T');
  $pdf->SetX(31.5);
  $pdf->Box(100, '', "REVISAO DOS 50.000 KM (1,00HR)", 0, 'BLR', 'L', 85, ['helvetica', '', 6], 'T');
  $pdf->SetX(131.5);
  $pdf->Box(21, '', "10", 0, 'BLR', 'R', 85, ['helvetica', '', 6], 'T');
  $pdf->SetX(152.5);
  $pdf->Box(21, '', "151,10", 0, 'BLR', 'R', 85, ['helvetica', '', 6], 'T');
  $pdf->SetX(173.5);
  $pdf->Box(26.5, '', "1510,10", 1, 'BLR', 'R', 85, ['helvetica', '', 6], 'T');


  $pdf->Box(189.5, '', '', 1, 'TLBR', 'C', 11);

  $y = $pdf->GetY();
  $pdf->SetY($y-10);
  $pdf->SetX(11.5);

  $pdf->Box(36, 'PIS (0,0000%):', 'R$ 0,00', 0, 'TLBR', 'C', 9, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 8], 'C');
  $pdf->SetX(49);
  $pdf->Box(36, 'COFINS (0,0000%):', 'R$ 0,00', 0, 'TLBR', 'C', 9, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 8], 'C');
  $pdf->SetX(87);
  $pdf->Box(36, 'INSS (0,0000%):', 'R$ 0,00', 0, 'TLBR', 'C', 9, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 8], 'C');
  $pdf->SetX(124);
  $pdf->Box(36, 'IR (0,0000%):', 'R$ 0,00', 0, 'TLBR', 'C', 9, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 8], 'C');
  $pdf->SetX(161);
  $pdf->Box(36, 'CSLL (0,0000%):', 'R$ 0,00', 1, 'TLBR', 'C', 9, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 8], 'C');


  $pdf->Box(189.5, '', 'VALOR TOTAL DA NOTA = R$ ' . '1000', 1, 'BLR', 'C', 7, ['helvetica', 'B', 10]);


  $pdf->Box(189.5, '', '', 1, 'TLBR', 'C', 11);

  $y = $pdf->GetY();
  $pdf->SetY($y-10);
  $pdf->SetX(11.5);

  $pdf->Box(36, 'Valor Total Composição:', 'R$ 0,00', 0, 'TLBR', 'R', 9, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 7]);
  $pdf->SetX(49);
  $pdf->Box(36, 'Valor Total Deduções:', 'R$ 0,00', 0, 'TLBR', 'R', 9, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 7]);
  $pdf->SetX(87);
  $pdf->Box(36, 'Base Cálculo:', 'R$ 151,11', 0, 'TLBR', 'R', 9, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 7]);
  $pdf->SetX(124);
  $pdf->Box(36, 'Alíquota:', '5,00%', 0, 'TLBR', 'R', 9, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 7]);
   $pdf->SetX(161);
  $pdf->Box(36, 'Valor ISS:', 'R$ 7,56', 1, 'TLBR', 'R', 9, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 7]);

  $yO = $pdf->GetY();
  $pdf->Box(189.5, '', "\nOUTRAS INFORMAÇÕES", 1, 'BLR', 'C', 50, ['helvetica', 'B', 9], 'T');
  

  //--------------------------------------
  // Prestador
  //--------------------------------------
  //$p = $emitente;
  //$endereco = "$p->logradouro, $p->numero $p->complemento - $p->bairro - $p->cep";
  //$pdf->Image(UPLOAD_PATH . $filial->logo, 25, $yP+2, 26, 26);
  $pdf->SetY($yP+3);
  $pdf->Columns(
    [0 => 32, 5 => 100, 10 => 120, 15 => 150], 
    [
      [ 0 => ['Nome / Razão Social:', 'TOYOLEX AUTOS S/A']], 
      [ 0 => ['CPF / CNPJ:', '07.234.453/0013-65'], 10 => ['Inscrição Municipal:', '27970001']],
      [ 0 => ['Endereço:', 'DOS HOLANDESES, QUADRA 31 LOTE 20 - BAIRRO CALHAU - CEP: 65071380']],
      // bug: na ultima linha o 'h' sempre dispara text overflow ...
      [ 0 => ['Município:', ' SÃO LUIS '], 5 => ['UF:', 'MA'], 10 => ['Email:' , $nota->emailtomador], 15 => ['Telefone:' , $nota->emailtomador]]
    ], 
    ['helvetica', '', 8], ['helvetica', 'B', 7], 5);



  //--------------------------------------
  // Tomador
  //--------------------------------------
 // $p = $tomador;
  //$endereco = "$p->logradouro, $p->numero $p->complemento - $p->bairro - $p->cep";
  $pdf->SetY($yT+3);
  $pdf->Columns([0 => 11, 5 => 85, 10 => 105, 15 => 165], [
    [0 => ['Nome / Razão Social:', 'STEFERSON LIMA COSTA FERREIRA']], 
    [0 => ['CPF / CNPJ:', '822.569.693-04'], 10 => ['Inscrição Municipal:', $nota->inscricaomunicipaltomador]],
    [0 => ['Endereço:', 'R DOS SABIAS ED PONTA NEGRA, APT 203 - BAIRRO RENASCENCA II - CEP: 65075760']],
    [0 => ['Município:', 'SÃO LUÍS'], 5 => ['UF:', 'MA'], 10 => ['Email:' , 'steferson.fereira@gmail.com'], 15 => ['Telefone:' , $nota->emailtomador]]
  ], ['helvetica', '', 8], ['helvetica', 'B', 7]);

  //--------------------------------------
  // Descriminação
  //--------------------------------------
  $pdf->SetY($yD+5);
  $pdf->Box(0, '', 'Descrição:O.S.: 341852 VENDEDOR: JACILENE RIBEIRO DA CONDICAO DE PAGAMENTO: MASTERCARD 2X PLACA:OXX2605 CHASSI:9BRBDWHE0F0231532 KM:54458 TRIB APROX R$ 20,32 FED 0,00 EST 7,30 MUN FONTE: IBPT 5A16F8', 1, 0, 'L', 70, ['helvetica', '', 5], 'T');

  //--------------------------------------
  // Outras Informaçoes
  //--------------------------------------
  $pdf->SetY($yO+8);
  $pdf->Columns(
    [0 => 11, 5 => 87, 10 => 150], 
    [
      ['h' => 4, 0 => ['Descrição NBS:', '']],
      ['h' => 4, 0 => ['Local de Incidência Imposto:', 'Estabelecimento do Prestador'], 5 => ['Tributação:', 'TRIBUTÁVEL'], 10 => ['Mês de','09/2019']],
      ['h' => 4, 0 => ['Local de Prestação do Serv.:', 'SAO LUIS / MA']],
      ['h' => 4, 0 => ['Recolhimento:', 'PRÓPRIO']],
      ['h' => 4, 0 => ['Atividade:', '452000100 - SERVICOS DE MANUTENCAO E REPARACAO MECANICA DE VEICULOS AUTOMOTORES']],
      ['h' => 12, 0 => ['Serviço:', '1401 - LUBRIFICACAO, LIMPEZA, LUSTRACAO, REVISAO, CARGA E RECARGA, CONSERTO, RESTAURACAO, BLINDAGEM,']],
      ['h' => 5, 0 => ['RPS/SÉRIE:', '209996/99 (18/09/2019)']],  
    ], 
    ['helvetica', '', 7], ['helvetica', '', 7], 5);



  // $txtPdf = $pdf->Output('nfse.pdf', 'S');
  // file_put_contents(UPLOAD_PATH . $target, $txtPdf);
  
  echo $pdf->Output('nfse.pdf', 'I');

    }


  public static function trataRetorno($response, $save = '')
    {
        $dados = [];
        if (empty($response)) {
            $dados[0] = "Sem resposta";
            return $dados;
        }
        var_dump($response); die;
        $std = json_decode($response);
        echo $std; die;


        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($std->body);

        $dados[0] = $std->url;
        $dados[1] = $std->operation;
        $dados[2] = $std->action;
        $dados[3] = $std->soapver;

        foreach ($std->parameters as $key => $param) {
            $html = "[$key] => $param <br>";
        }
        $dados[4] = $html;
        $dados[5] = $std->header;
        /*$html .= "<br>";
        $html .= '<h2>namespaces</h2>';
        $an = \Safe\json_decode(\Safe\json_encode($std->namespaces), true);
        foreach ($an as $key => $nam) {
            $html .= "[$key] => $nam <br>";
        }*/
        $html = "<br>";
        $html .= '<h2>body</h2>';
        $html .= str_replace(
            ['<', '>'],
            ['&lt;','&gt;'],
            str_replace(
                '<?xml version="1.0"?>',
                '<?xml version="1.0" encoding="UTF-8"?>',
                $doc->saveXML()
            )
        );
        $html .= "</pre>";

        $dados[6] = $html;
        return $dados;
    }
}
