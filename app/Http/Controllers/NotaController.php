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

      //$this->middleware('auth');
      
      $this->repository = new NotaRepository();

      parent::__construct();

      //homolog
      //$token = '3579F09B4CC37151D3327197B13F9583';

      //1-producao, 2-homologacao
      $this->ambiente = 2;
      //P-producao, H-homologacao
      $this->dadosEmissor = $this->repository->buscaDadosEmissor($this->ambiente);

      $this->config = [
                'cnpj' => $this->dadosEmissor->CNPJ,
                'im' => $this->dadosEmissor->IM,
                'cmun' => $this->dadosEmissor->CMUN, //ira determinar as urls e outros dados
                'razao' => $this->dadosEmissor->RAZAOSOCIAL,
                'tpamb' => $this->ambiente, //1-producao, 2-homologacao
                'token' => $this->dadosEmissor->TOKEN,
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
        $dados = json_decode(json_encode($this->repository->buscaDadosIniciais()));   
        //var_dump($dados);
        return view('notas.index', compact('dados'));

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


        $notasPeriodo = $this->repository->buscaNotaEmitida($idcliente, 
                        $dtInicial, $dtFinal);

        $msgAlerta = [];
        if ($this->repository->count > 0){
            
          array_push($msgAlerta, 'Existem nota(s) emitidas para esse período:' );

          foreach ($notasPeriodo as $nota) {

              $msg =  'NumNota: '. $nota->NUMERONOTA.
                  ' <br>  Dt. Nota: '.$nota->DTANOTA.'  '.
                  ' Valor: '. $nota->VALORNOTA;

              if($nota->NUMERO_NFSE)
                  $msg .=' <br>  NFSE: '. $nota->NUMERO_NFSE.'  '.
                  '   Código Verificação:'.$nota->CODIGOVERIFICACAO.
                  ' <br>link: <a href="/notas/consultarnfse/'.$nota->NUMERO_NFSE.'/'.$nota->CODIGOVERIFICACAO.'/"> Acesse</a>';

              else
                  $msg .= "<br> Sem NFSE cadastrada.";

              array_push($msgAlerta, $msg);
          }
        }
          

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
                      'dataNota', 'horaNota', 'idcliente',
                      'msgAlerta'
                    ));

    }

    public function preConsultarNotasEmitidas()
    {
        $clientes = $this->repository->consultarClientesCompleto();

        return view('notas.pre-consnotasemitidas', compact('clientes'));
    }


    public function posConsultarNotasEmitidas(Request $request)
    {

        $dtIni = $request->dtIni;
        $dtFim = $request->dtFim;

        $notas = $this->repository->consultarNotasEmitidas($request->dtIni, 
                $request->dtFim, $request->cmbCliente);

        return view('notas.pos-consnotasemitidas', compact('notas','dtIni','dtFim'));
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

        
            
        /*
            $buscaNota = $this->repository->verificaNotaEmitida($request->numeronota);

            if ($buscaNota == true)
                return redirect()->back()->withErrors(['Já existe uma nota com este número']);
        */
  /*          $tools = new Tools($this->configJson, $this->cert);

            $arps = [];
            
            $std = new \stdClass();
            $std->dtInicial = $request->has('dtInicial')? $request->dtInicial : "";
            $std->dtFinal = $request->has('dtFinal')? $request->dtFinal : "";
            $std->qtdade = $request->has('qtdade')? $request->qtdade : 1;
            $std->vTotServ = $this->formataValor($request->has('vTotServ')? $request->vTotServ : 0.00);
            $std->vTotDeduc = $this->formataValor($request->has('vTotDeduc')? $request->vTotDeduc : 0.00);


            $std->inscricaomunicipalprestador = $request->has('inscricaomunicipalprestador')? $request->inscricaomunicipalprestador : "";
            $std->razaosocialprestador = htmlentities($request->has('razaosocialprestador')? $request->razaosocialprestador : "");

            $dataEmissao = $request->has('dataemissaorps')? $request->dataemissaorps :'01/01/2020';
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
            $std->razaosocialtomador = htmlentities($request->has('razaosocialtomador')? $request->razaosocialtomador : "");
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
            $std->itens[0]->discriminacaoservico = $request->has('discriminacaoservico')? $request->discriminacaoservico : "";
            $std->itens[0]->quantidade = $request->has('quantidade')? $request->quantidade : 1;
            $std->itens[0]->valorunitario = $this->formataValor($request->has('valorunitario')? $request->valorunitario : 0.00);
            $std->itens[0]->valortotal =  $this->formataValor($request->has('valortotal')? $request->valortotal : 0.00);
            $std->itens[0]->tributavel = $request->has('tributavel')? $request->tributavel : "S";


            $rps = new Rps($std);

            $arps[] = $rps;    
            $lote = date('ymdHis');
            $ret = '

<s:envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:body><ns2:enviarresponse xmlns:ns2="http://sistemas.semfaz.saoluis.ma.gov.br/WsNFe2/LoteRps.jws"><enviarreturn><!--?xml version="1.0" encoding="UTF-8"?-->
<reqenvioloterps xmlns:ns1="http://sistemas.semfaz.saoluis.ma.gov.br/WsNFe2/lote" xmlns:tipos="http://sistemas.semfaz.saoluis.ma.gov.br/WsNFe2/tp" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemalocation="http://sistemas.semfaz.saoluis.ma.gov.br/WsNFe2/xsd/RetornoConsultaLote.xsd ">
  <Cabecalho xmlns="" Versao="1">
        <Sucesso>true</Sucesso>
        <InformacoesLote>
            <NumeroLote>42686544</NumeroLote>
            <InscricaoPrestador>39617106</InscricaoPrestador>
            <CPFCNPJRemetente>
                <CNPJ>99999998000228</CNPJ>
            </CPFCNPJRemetente>
            <DataEnvioLote>2015-01-26T15:42:44</DataEnvioLote>
            <QtdNotasProcessadas>2</QtdNotasProcessadas>
            <TempoProcessamento>1</TempoProcessamento>
            <ValorTotalServicos>201</ValorTotalServicos>
        </InformacoesLote>
    </Cabecalho>
    <ChaveNFeRPS xmlns="">
        <ChaveNFe>
            <InscricaoPrestador>39617106</InscricaoPrestador>
            <NumeroNFe>3</NumeroNFe>
            <CodigoVerificacao>2QFFXUMK</CodigoVerificacao>
        </ChaveNFe>
        <ChaveRPS>
            <InscricaoPrestador>39617106</InscricaoPrestador>
            <SerieRPS>BB</SerieRPS>
            <NumeroRPS>4102</NumeroRPS>
        </ChaveRPS>
    </ChaveNFeRPS>
</reqenvioloterps></enviarreturn></ns2:enviarresponse></s:body></s:envelope>
'; 
            $response = $this->trataRetorno($tools->enviar($arps, $lote), 'enviarReturn');
            //$response = $this->trataRetorno($ret, 'enviarreturn');
*/

            $lote = date('ymdHis');
            $response = '{"Cabecalho":{"CodCidade":"921","Sucesso":"true","NumeroLote":"248207723","CPFCNPJRemetente":"01469892000137","DataEnvioLote":"2020-02-20T10:40:47","QtdNotasProcessadas":{},"TempoProcessamento":"1","ValorTotalServicos":"6.44","ValorTotalDeducoes":"0","Versao":"1","Assincrono":"N"},"Alertas":{},"Erros":{},"ChavesNFSeRPS":{"ChaveNFSeRPS":{"ChaveNFe":{"InscricaoPrestador":"7048009","NumeroNFe":"20506","CodigoVerificacao":"FE060BD12C428B08A1DCC2B8E54EC018","RazaoSocialPrestador":"BRITO E SOARES LTDA"},"ChaveRPS":{"InscricaoPrestador":"7048009","SerieRPS":"99","NumeroRPS":"3","DataEmissaoRPS":"20\/02\/2020","RazaoSocialPrestador":"BRITO E SOARES LTDA"}}}}' ;
            $response = json_decode($response);


            $fp = fopen('nfe_emitidas/'.$request->idcliente.'-'.$lote.'.json', 'w');
            fwrite($fp, json_encode($response));
            fclose($fp);


            $retorno = "";
            $retorno = $response->Cabecalho;
            if($retorno->Sucesso == 'N' || $retorno->Sucesso == 'false'){
                $erros = [];
                foreach ($retorno->Erros as $erro) {
                  array_push($erros, $erro);
                }
                return redirect()->back()->withErrors($erros);
            }

            //salvar nota
            $chave = $response->ChavesNFSeRPS->ChaveNFSeRPS;



            $retornoSalvar = $this->repository->salvaNotaEmitida($request->idcliente,  
              $chave->ChaveNFe->NumeroNFe, 
              $this->formataValor($request->vTotServ),
              $request->dtInicial,
              $request->dtFinal, 
              0,
              $request->dataemissaorps, 5, $chave->ChaveNFe->NumeroNFe, 
              $chave->ChaveNFe->CodigoVerificacao, json_encode($response));

            if ($retornoSalvar[0] == false){
                return redirect()->back()->withErrors([$retornoSalvar[1]]);
            }

            //retorna a página inicial
            $msgInforma = [];

            array_push($msgInforma, 'Nota emitida com sucesso.');
            array_push($msgInforma, 'NFSe: '.$chave->ChaveNFe->NumeroNFe.
                ' Código de Verificação:'.$chave->ChaveNFe->CodigoVerificacao);
            array_push($msgInforma, 'Para imprimir <a href="/nota/imprimirnota/'.$chave->ChaveNFe->NumeroNFe.'/'.$chave->ChaveNFe->CodigoVerificacao.'/" target="_blank">clique aqui</a>');

            dd();

        } catch (\Exception $e) {

            return redirect()->back()->withErrors([$e->getMessage()]);
        }

        return view('notas.index', 
                    compact('msgInforma'));

    }

    public function preConsultarNfse()
    {
        return view('notas.pre-consultarnfse');
    }


    public function posConsultarNfse(Request $request)
    {

        $tools = new Tools($this->configJson, $this->cert);

        $dtIni = $this->formataDataEnvio($request->dtIni);
        $dtFim = $this->formataDataEnvio($request->dtFim);

        $notas = $this->trataRetorno($tools->consultarNota($dtIni, $dtFim), 'consultarNotaReturn');
        //dd($notas);
        if ($this->trataItemVazio($notas->NotasConsultadas) !="")
            $notas = $notas->NotasConsultadas->Nota; 
        else
            $notas = [];

        return view('notas.pos-consultarnfse', compact('notas'));
    }

   public function imprimirNfse($numnota, $codigo)
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


    public function  danfse($nota){//, $target) {

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
          [ 0 => ['Nome / Razão Social:',  $dadosEmissor->RAZAOSOCIAL]], 
          [ 0 => ['CPF / CNPJ:', $dadosEmissor->CNPJ], 10 => ['Inscrição Municipal:',$dadosEmissor->IM]],
          [ 0 => ['Endereço:', $dadosEmissor->ENDERECO]],
          // bug: na ultima linha o 'h' sempre dispara text overflow ...
          [ 0 => ['Município:', $dadosEmissor->MUNICIPIO], 5 => ['UF:', $dadosEmissor->UF], 10 => ['Email:' , $dadosEmissor->EMAIL], 15 => ['Telefone:' , $dadosEmissor->TELEFONE]]
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
          ['h' => 4, 0 => ['Atividade:', $dadosEmissor->COD_ATIVIDADE.' '.$dadosEmissor->DESC_ATIVIDADE]],
          ['h' => 12, 0 => ['Serviço:', $dadosEmissor->COD_SERVICO.' '.$dadosEmissor->DESC_COD_SERVICO]],
          ['h' => 5, 0 => ['RPS/SÉRIE:', $nota->NumeroRPS.'/'.$nota->SerieRPS.'('.$this->formataDataRetono($nota->DataProcessamento).')']],  
        ], 
        ['helvetica', '', 7], ['helvetica', '', 7], 5);



    //concatena o faturamento junto a nota

    $pdf->AddPage();


    $dadosNota = $this->repository->buscaDadosNotaNfse($nota->NumeroNota);

    if ($this->repository->count >0){

        $dadosFaturamento = $this->repository->consultaFaturamentoNota( 
                                    $dadosNota->DTAINICIAL, 
                                    $dadosNota->DTAFINAL,
                                    $dadosNota->CODCLIENTE);

        //var_dump($dadosFaturamento);

        $periodo = "Período: ".$dadosNota->DTAINICIAL." - ".$dadosNota->DTAFINAL;

        $html = '
            <table class="tabela"  cellpadding="10" style="vertical-align: top; 
               font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px;">
               <tr>
                <td style="padding: 12px; display: inline-block; width:60%;">
                  <p style="margin-bottom: 0.75rem;"><b>'.$dadosFaturamento->FANTASIA.'</b></p>
                                <p style="margin-top: -0.375rem; margin-bottom: 0;">Razão: '.$dadosFaturamento->NOME.'</p>
                                <p style="margin-bottom: 10px;">'.$periodo.'</p>
                                </td>
                <td>
                <img src="/images/logo.gif"></td>
               </tr>
            </table>
              <div style="font-family: Verdana, Arial, Helvetica, sans-serif;font-size: 14px;">
                        <p style="text-align:center;"><br><b>FATURAMENTO CONSOLIDADO</b></p>
              </div>
              <table cellpadding="1px" cellspacing="0px" style="vertical-align: top; border: 1px solid; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px;">
                  <thead >
                        <tr style="border: 1px solid;">
                                <th width="70%">Material</th>
                          <th width="10%">Unitário</th>
                          <th width="10%">Qtd</th>
                          <th width="10%">Valor</th>
                          </tr>
                       </thead><tbody>';

                $dadosItens = $this->repository->consultaFaturamentoNotaItens(
                                    $dadosNota->DTAINICIAL, 
                                    $dadosNota->DTAFINAL,
                                    $dadosNota->CODCLIENTE);
                foreach ($dadosItens as $item) {
                    $html .= '<tr>
                              <td>&nbsp;'.$item->NOME.'</td>
                              <td style="text-align: right;">'.$item->VAL_UNITARIO.'</td>
                              <td style="text-align: center;">'.$item->QTD.'</td>
                              <td style="text-align: right;">'.$item->TOTAL.'</td>
                              </tr>';  
                }   

              $html .= '
              </tbody>

                    <tfoot>
                      <tr>
                             
                          <th></th>
                          <th></th>
                          <th>Total:</th>
                          <th style="text-align: right;">'.$dadosFaturamento->TOTAL.'</th>
                      </tr> 
                      <tr>
                          <th>&nbsp;Recebido por: ____________________________________</th>
                          <th></th>
                          <th>Desconto:</th>
                          <th style="text-align: right;">'.$dadosFaturamento->DESCONTO.'</th>
                      </tr>
                      <tr>
                          <th></th>
                          <th></th>
                          <th>Transporte:</th>
                          <th style="text-align: right;">'.$dadosFaturamento->TRANSPORTE.'</th>
                      </tr> 
                      <tr>
                          <th>&nbsp;Em: _____ / _____ / _______</th>
                          <th></th>
                          <th>Total a Pagar:</th>
                          <th style="text-align: right;">'.$dadosFaturamento->TOTALD.'</th>
                      </tr> 
                    </tfoot>
                  </table> ';

        // output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');
    }



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

        $node = str_replace("<".$chave.">", "", $node);
        $node = str_replace("</".$chave.">", "", $node);
        $node = ltrim($node);

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
    
      return number_format((float)$valor, 2, ',', '.');
    } 

    function trataItemVazio($valor) {
      if (empty((array) $valor)) 
        $valor = "";
            
      return $valor;
    } 
    
}