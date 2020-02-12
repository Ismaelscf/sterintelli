<?php
namespace App\Http\Controllers;

use App\Report;
use App\Config;
use Illuminate\Http\Request;
use NFePHP\Common\Certificate;
use NFePHP\NFSeDSF\Rps;
use NFePHP\NFSeDSF\Common\Soap\SoapCurl;
use NFePHP\NFSeDSF\Tools;
use NFePHP\NFSeDSF\Common\Standardize;
use stdClass;
use DOMDocument;
use DOMXpath;

use App\Repositories\NotaRepository;

class NotaController extends Controller
{
    
    protected $config;
    protected $configJson;
    protected $cert;
    protected $repository;
    protected $ambiente;
    protected $dadosEmissor;


    public function __construct(stdClass $rps)
    {
      $this->repository = new NotaRepository();

      //homolog
      //$token = '3579F09B4CC37151D3327197B13F9583';

      //1-producao, 2-homologacao
      $this->ambiente = 2;
      //P-producao, H-homologacao
      $this->dadosEmissor = $this->repository->buscaDadosEmissor($this->ambiente);

      $this->config = [
                'cnpj' => $this->dadosEmissor['CNPJ'],
                'im' => $this->dadosEmissor['IM'],
                'cmun' => $this->dadosEmissor['CMUN'], //ira determinar as urls e outros dados
                'razao' => $this->dadosEmissor['RAZAOSOCIAL'],
                'tpamb' => $this->ambiente, //1-producao, 2-homologacao
                'token' => $this->dadosEmissor['TOKEN'],
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
        //$notas = $this->repository->buscaNotas();   
        return view('notas.index');//, compact('notas'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function preEmitir($idcliente, Request $request)
    {
        $dtInicial = $request->query->has('dtini') ? 
                          $request->query->all()['dtini'] : null;
        $dtFinal = $request->query->has('dtfim') ? 
                          $request->query->all()['dtfim'] : null;

        $dadosEmissor = $this->dadosEmissor;
        
        /*buscar os dados do período*/
        $dadosEmissao = $this->repository->buscaDadosEmissao(
                                $idcliente, $dtInicial, $dtFinal
                              );

        $numRps = (int)$this->consultarUltimoSeqRps() + 1;
        $dataNota = date("d/m/Y");
        $horaNota = date("H:i:s");

        return view('notas.pre-emitir', 
                    compact('dadosEmissor', 'dadosEmissao', 
                      'dtInicial', 'dtFinal', 'numRps',
                      'dataNota', 'horaNota'));  

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function posEmitir(Request $request)
    {
  
        try {

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
            $std->vTotServ = $this->formataValor($request->has('vTotServ')? $request->vTotServ : 0.00);
            $std->vTotDeduc = $this->formataValor($request->has('vTotDeduc')? $request->vTotDeduc : 0.00);


            $std->inscricaomunicipalprestador = $request->has('inscricaomunicipalprestador')? $request->inscricaomunicipalprestador : "";
            $std->razaosocialprestador = $request->has('razaosocialprestador')? $request->razaosocialprestador : "";

            $dataEmissao = $request->has('dataemissaorps')? $request->dataemissaorps :'2020-01-01';
            $horaEmissao = $request->has('horaemissaorps')? $request->horaemissaorps :'00:00:00';

            $dataEmissao = $this->formataDataHoraEnvio($dataEmissao, $horaEmissao);

            //dados do RPS
            $std->tiporps = $request->has('tiporps')? $request->tiporps : "RPS";
            $std->serierps = $request->has('serierps')? $request->serierps : "NF";
            $std->numerorps = $request->has('numerorps')? $request->numerorps : 1;
            $std->dataemissaorps = $dataEmissao;
            $std->situacaorps = $request->has('situacaorps')? $request->situacaorps :'N';
            $std->serieprestacao = $request->has('serieprestacao')? $request->serieprestacao :'99';

            //DADOS DO TOMADOR
            $std->inscricaomunicipaltomador = $request->has('inscricaomunicipaltomador')? $request->inscricaomunicipaltomador : "000000";
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
            $std->valorpis = $this->formataValor($request->has('valorpis')? $request->valorpis : 0.00);
            $std->valorcofins = $this->formataValor($request->has('valorcofins')? $request->valorcofins : 0.00);
            $std->valorinss = $this->formataValor($request->has('valorinss')? $request->valorinss : 0.00);
            $std->valorir = $this->formataValor($request->has('valorir')? $request->valorir : 0.00);
            $std->valorcsll = $this->formataValor($request->has('aliquotapis')? $request->aliquotapis : 0.00);
            $std->aliquotapis = $this->formataValor($request->has('aliquotapis')? $request->aliquotapis : 0.00);
            $std->aliquotacofins = $this->formataValor($request->has('aliquotacofins')? $request->aliquotacofins : 0.00);
            $std->aliquotainss = $this->formataValor($request->has('aliquotainss')? $request->aliquotainss : 0.00);
            $std->aliquotair = $this->formataValor($request->has('aliquotair')? $request->aliquotair : 0.00);
            $std->aliquotacsll = $this->formataValor($request->has('aliquotacsll')? $request->aliquotacsll :0.00);
            $std->descricaorps = $request->has('descricaorps')? $request->descricaorps : "";

            $std->itens[0] = new stdClass();
            $std->itens[0]->discriminacaoservico = $request->has('descricaorps')? $request->descricaorps : "";
            $std->itens[0]->quantidade = $request->has('quantidade')? $request->quantidade : 1;
            $std->itens[0]->valorunitario = $this->formataValor($request->has('valorunitario')? $request->valorunitario : 0.00);
            $std->itens[0]->valortotal =  $this->formataValor($request->has('valortotal')? $request->valortotal : 0.00);
            $std->itens[0]->tributavel = $request->has('tributavel')? $request->tributavel : "S";


            $rps = new Rps($std);

            $arps[] = $rps;    
            $lote = date('ymdHis');
            $response = $this->trataRetorno($tools->enviar($arps, $lote));



        } catch (\Exception $e) {
            echo $e->getMessage();
        }
   
        return  $response;//

        //modelo de retorno com sucesso
        //redirect()->route('notas.index')
                        //->with('success','Nota criada com sucesso.');
    }

    public function preConsultarNotas()
    {
        return view('notas.pre-consultarnota');
    }


    public function posConsultarNotas(Request $request)
    {

        $tools = new Tools($this->configJson, $this->cert);

        $dtIni = $this->formataDataEnvio($request->dtIni);
        $dtFim = $this->formataDataEnvio($request->dtFim);

        $notas = $this->trataRetorno($tools->consultarNota($dtIni, $dtFim), 'consultarNotaReturn');
        //var_dump($notas);
        $notas = $notas->NotasConsultadas->Nota;
        return view('notas.pos-consultarnota', compact('notas'));
    }

   public function consultarNfse($numnota, $codigo)
    {

        $notas[0] = ['numero' => $numnota, 
                     'codigo' => $codigo];

        $tools = new Tools($this->configJson, $this->cert);

        $nota = $this->trataRetorno($tools->consultarNFSeRps($notas), 
          'consultarNFSeRpsReturn');

        $this->danfse($nota);
    }

    //TODO método precisa ser checado pois não está cancelando via 
    //RPS
    public function cancelarNota($numnota, $codigo, Request $request)
    {
        $tools = new Tools($this->configJson, $this->cert);

        $numero = $numnota;
        $motivo = $request->motivo;
        $codigoverificacao = $codigo;
    
        $response = $this->trataRetorno($tools->cancelar($numero, $motivo, $codigoverificacao));

        return view('notas.cancelar');
    }


    public function  danfse ($nota){//, $target) {

        $cnpj = $nota->Cabecalho->CPFCNPJRemetente;
        $nota = $nota->NotasConsultadas->Nota;
        $dadosEmissor = $this->dadosEmissor;
        //print_r($nota);

        $pdf = new Report(new Config('NFSe'));
        $pdf->SetMargins(10.5, 3, 20);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(False, 0);
        $pdf->AddPage();
        $pdf->SetCellPaddings(0, 0, 0, 0);
        $pdf->SetLineStyle(array('width' => 0.3));
  
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
  $pdf->Box(75, 'Número da Nota', sprintf('%06d', $nota->NumeroNota), 1, 'TBR', 'L', 10, ['helvetica', 'B', 10], 'B', false, ['helvetica', '', 9]);
  $pdf->SetX($col2);
  $pdf->Box(75, 'Data e Hora da Emissão', $this->formataDataRetono($nota->DataProcessamento), 1, 'TBR', 'L', 10, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 9]);
  $pdf->SetX($col2);
  $pdf->Box(75, 'Código de Verificação', $nota->CodigoVerificacao, 1, 'TBR', 'L', 12, ['helvetica', 'B', 5], 'B', false, ['helvetica', '', 9]);

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

  $itens = $nota->Itens;
  $totalNota = 0;

  foreach ($itens as $item) {

        // Itens
        //---------------
        $tributavel = $item->Tributavel == "S"?"TRIBUTÁVEL": "NÃO TRIBUTÁVEL";
        $pdf->Box(21, '', $tributavel, 0, 'BLR', 'L', 85, ['helvetica', '', 6], 'T');
        $pdf->SetX(31.5);
        $pdf->Box(100, '', $item->DiscriminacaoServico, 0, 'BLR', 'L', 85, ['helvetica', '', 6], 'T');
        $pdf->SetX(131.5);
        $pdf->Box(21, '', $item->Quantidade, 0, 'BLR', 'R', 85, ['helvetica', '', 6], 'T');
        $pdf->SetX(152.5);
        $pdf->Box(21, '', $this->formataCurrency($item->ValorUnitario), 0, 'BLR', 'R', 85, ['helvetica', '', 6], 'T');
        $pdf->SetX(173.5);
        $pdf->Box(26.5, '', $this->formataCurrency($item->ValorTotal), 1, 'BLR', 'R', 85, ['helvetica', '', 6], 'T');
        
        $totalNota += (float)$item->ValorTotal;
  }

  $pdf->Box(189.5, '', '', 1, 'TLBR', 'C', 11);

  $y = $pdf->GetY();
  $pdf->SetY($y-10);
  $pdf->SetX(11.5);

  $pdf->Box(36, 'PIS ('.$nota->AliquotaPIS.'%):', 'R$ '.$this->formataCurrency($nota->ValorPIS).'', 0, 'TLBR', 'C', 9, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 8], 'C');
  $pdf->SetX(49);
  $pdf->Box(36, 'COFINS ('.$nota->AliquotaCOFINS.'%):', 'R$ '.$this->formataCurrency($nota->ValorCOFINS).'', 0, 'TLBR', 'C', 9, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 8], 'C');
  $pdf->SetX(87);
  $pdf->Box(36, 'INSS ('.$nota->AliquotaINSS.'0%):', 'R$ '.$this->formataCurrency($nota->ValorINSS).'', 0, 'TLBR', 'C', 9, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 8], 'C');
  $pdf->SetX(124);
  $pdf->Box(36, 'IR ('.$nota->AliquotaIR.'%):', 'R$ '.$this->formataCurrency($nota->ValorIR).'', 0, 'TLBR', 'C', 9, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 8], 'C');
  $pdf->SetX(161);
  $pdf->Box(36, 'CSLL ('.$nota->AliquotaCSLL.'%):', 'R$ '.$this->formataCurrency($nota->ValorCSLL).'', 1, 'TLBR', 'C', 9, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 8], 'C');


  $pdf->Box(189.5, '', 'VALOR TOTAL DA NOTA = R$ ' . $this->formataCurrency($totalNota), 1, 'BLR', 'C', 7, ['helvetica', 'B', 10]);


  $pdf->Box(189.5, '', '', 1, 'TLBR', 'C', 11);

  $y = $pdf->GetY();
  $pdf->SetY($y-10);
  $pdf->SetX(11.5);

  $devolucao = $this->trataItemVazio($nota->Deducoes);
  $alqiss = (float)$nota->AliquotaAtividade;
  $valiss = $totalNota*$alqiss;

  $pdf->Box(36, 'Valor Total Composição:', 'R$ '.$this->formataCurrency($totalNota).'', 0, 'TLBR', 'R', 9, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 7]);
  $pdf->SetX(49);
  $pdf->Box(36, 'Valor Total Deduções:', 'R$ '.$this->formataCurrency($devolucao).'', 0, 'TLBR', 'R', 9, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 7]);
  $pdf->SetX(87);
  $pdf->Box(36, 'Base Cálculo:', 'R$ '.$this->formataCurrency($totalNota).'', 0, 'TLBR', 'R', 9, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 7]);
  $pdf->SetX(124);
  $pdf->Box(36, 'Alíquota:', ''.$nota->AliquotaAtividade.'%', 0, 'TLBR', 'R', 9, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 7]);
   $pdf->SetX(161);
  $pdf->Box(36, 'Valor ISS:', 'R$ '.$this->formataCurrency($valiss).'', 1, 'TLBR', 'R', 9, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 7]);

  $yO = $pdf->GetY();
  $pdf->Box(189.5, '', "\nOUTRAS INFORMAÇÕES", 1, 'BLR', 'C', 50, ['helvetica', 'B', 9], 'T');
  

  //--------------------------------------
  // Prestador
  //--------------------------------------
  //$pdf->Image(UPLOAD_PATH . $filial->logo, 25, $yP+2, 26, 26);
  $pdf->SetY($yP+3);
  $pdf->Columns(
    [0 => 32, 3 => 70, 5 => 100, 10 => 120, 15 => 150], 
    [
      [ 0 => ['Nome / Razão Social:',  $dadosEmissor['RAZAOSOCIAL']]], 
      [ 0 => ['CPF / CNPJ:', $dadosEmissor['CNPJ']], 10 => ['Inscrição Municipal:',$dadosEmissor['IM']]],
      [ 0 => ['Endereço:', $dadosEmissor['ENDERECO']]],
      // bug: na ultima linha o 'h' sempre dispara text overflow ...
      [ 0 => ['Município:', $dadosEmissor['MUNICIPIO']], 5 => ['UF:', $dadosEmissor['UF']], 10 => ['Email:' , $dadosEmissor['EMAIL']], 15 => ['Telefone:' , $dadosEmissor['TELEFONE']]]
    ], 
    ['helvetica', '', 8], ['helvetica', 'B', 7], 5);



  //--------------------------------------
  // Tomador
  //--------------------------------------
 // $p = $tomador;
  $enderecoTomador = $nota->TipoLogradouroTomador." ". $nota->LogradouroTomador." ".
              $nota->NumeroEnderecoTomador." - ".$nota->ComplementoEnderecoTomador." ".
              $nota->BairroTomador;
  $pdf->SetY($yT+3);
  $pdf->Columns([0 => 11, 5 => 85, 10 => 105, 15 => 165], 
    [
        [0 => ['Nome / Razão Social:', $this->trataItemVazio($nota->RazaoSocialTomador)]], 
        [0 => ['CPF / CNPJ:', $this->trataItemVazio($nota->CPFCNPJTomador)], 
         10 => ['Inscrição Municipal:', $this->trataItemVazio($nota->InscricaoMunicipalTomador)]
        ],
        [0 => ['Endereço:', $enderecoTomador]],
        [0 => ['Município:', $this->trataItemVazio($nota->CidadeTomadorDescricao)], 
         5 => ['UF:', ''], 
         10 => ['Email:' , $this->trataItemVazio($nota->EmailTomador)], 
         15 => ['Telefone:' , '']
        ]
    ], 
    ['helvetica', '', 8], 
    ['helvetica', 'B', 7]);

  //--------------------------------------
  // Descriminação
  //--------------------------------------
  $pdf->SetY($yD+5);
  $pdf->Box(0, '', '', 1, 0, 'L', 70, ['helvetica', '', 5], 'T'); //$nota->DescricaoRPS

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
      ['h' => 4, 0 => ['Atividade:', $dadosEmissor['COD_ATIVIDADE'].' '.$dadosEmissor['DESC_ATIVIDADE']]],
      ['h' => 12, 0 => ['Serviço:', $dadosEmissor['COD_SERVICO'].' '.$dadosEmissor['DESC_COD_SERVICO']]],
      ['h' => 5, 0 => ['RPS/SÉRIE:', $nota->NumeroRPS.'/'.$nota->SerieRPS.'('.$this->formataDataRetono($nota->DataProcessamento).')']],  
    ], 
    ['helvetica', '', 7], ['helvetica', '', 7], 5);



    // $txtPdf = $pdf->Output('nfse.pdf', 'S');
    // file_put_contents(UPLOAD_PATH . $target, $txtPdf);
  
    echo $pdf->Output('nfse.pdf', 'I');

    }



   public function consultarUltimoSeqRps()
    {
        $tools = new Tools($this->configJson, $this->cert);
        $response = $this->trataRetorno($tools->consultarSequencialRps(), 'consultarSequencialRpsReturn');

        return  $response->Cabecalho->NroUltimoRps;
    }


  public function trataRetorno($response, $chave)
    {

        $response = html_entity_decode($response);
        //echo $response;

        $dom = new DOMDocument( '1.0', 'utf-8' );
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->recover = true;
        $dom->loadXML($response);

/*
        foreach($dom->getElementsByTagName('*') as $tag) {        
            
            if ($tag->nodeName != 'S:Envelope')
              echo " <br> <br>".$tag->nodeName." - ".$tag->nodeValue;
        }  */ 

        $node = $dom->getElementsByTagName($chave)->item(0);
        $node = $dom->saveXML($node);

        //print_r($node);

        $node = str_replace("<".$chave.">", "", $node);
        $node = str_replace("</".$chave.">", "", $node);
        $node = ltrim($node);

        //echo $node;

        $sxml = simplexml_load_string($node);
        if (false === $sxml) {
                echo "Failed loading XML\n";
                foreach(libxml_get_errors() as $error) {
                    echo "\t", $error->message;
                }
            }
        $json = str_replace(
        '@attributes',
        'attributes',
        json_encode($sxml, JSON_PRETTY_PRINT)
        );

        //echo $json;

        return json_decode($json);
    }


    function formataDataHoraEnvio($data, $hora) {
      $array = explode('/', $data);
      $dataFinal =  $array[2].'-'.$array[1].'-'.$array[0].'T'.$hora;
      return $dataFinal;
    }

    function formataDataEnvio($data) {
      $array = explode('/', $data);
      $dataFinal =  $array[2].'-'.$array[1].'-'.$array[0];
      return $dataFinal;
    }


    function formataDataRetono($data) {

      $array = explode('T', $data);

      $arrayD = explode('-', $array[0]);
      $dataFinal =  $arrayD[2].'/'.$arrayD[1].'/'.$arrayD[0].' '. substr($array[1], 0, -1);
      return $dataFinal;
    }


    function formataValor($valor) {

      $valor = str_replace('.', '', $valor);
      $valor = str_replace(',', '.', $valor);
            
      return number_format($valor, 2, '.', '');
    } 


    function formataCurrency($valor) {
      if ( $valor =="")
        $valor = 0;
      else
        $valor = str_replace('.', ',', $valor);
            
      return number_format($valor, 2, ',', '.');
    } 

    function trataItemVazio($valor) {
      if (empty((array) $valor)) 
        $valor = "";
            
      return $valor;
    } 
    
}