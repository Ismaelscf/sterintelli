<?php

namespace App\Http\Controllers;


use App\Nota;
use App\Controllers\Report;
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
        //$content = file_get_contents('/Users/steferson_1/dev_stef/certificado/expired_certificate.pfx');
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
        $dtInicial = date('Y-m-d H:i:s');
        $dtFinal = date('Y-m-d H:i:s');


        return view('notas.pre-emitir', compact('cnpjprestador', 'inscricaomunicipalprestador',
            'razaosocialprestador', 'token', 'dtInicial', 'dtFinal'));  

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function enviar(Request $request)
    {
  
        Nota::create($request->all());



        try {

            $soap = new SoapCurl();
            $soap->disableCertValidation(true);
            
            $tools = new Tools($this->configJson, $this->cert);
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
           
            $response = $tools->enviarSincrono($arps, $lote);


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
  $pdf->SetMargins(20, 12, 20);
  $pdf->setPrintHeader(false);
  $pdf->setPrintFooter(false);
  $pdf->SetAutoPageBreak(False, 0);
  $pdf->AddPage();
  $pdf->SetCellPaddings(2, 0, 2, 0);
  
  //--------------------------------------
  // Recibo
  //--------------------------------------
  $y = $pdf->GetY();
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
  $pdf->Ln(10);

  //--------------------------------------
  // Cabeçalho
  //--------------------------------------
  $y = $pdf->GetY();
  $pdf->Image(BACKEND . 'images/ctba.jpg', 25, $y+2, 26, 26);
  $pdf->Box(135, '', '', 0, 'TLBR', 'C', 30);
  $pdf->SetFont('helvetica', 'B', 12);
  $pdf->Text(60, $y+2, 'PREFEITURA MUNICIPAL DE SÃO LUÍS', false, false, true, 0, 1);
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Text(65, $pdf->GetY()+1, 'SECRETARIA MUNICIPAL DA FAZENDA', false, false, true, 0, 1);
  $pdf->SetFont('helvetica', 'B', 11);
  $pdf->Text(52, $pdf->GetY()+1, 'NOTA FISCAL DE SERVIÇOS ELETRÔNICA - NFS-e', false, false, true, 0, 1);
  $pdf->SetFont('helvetica', '', 8);
  $pdf->Text(62, $pdf->GetY()+1,
    "RPS nº $nota->numerorps, Série $nota->serierps, emitido em " . ymdDmy($nota->dataemissaorps) . ' às ' . ymdH($nota->dataemissaorps)
  );

  $pdf->SetY($y);
  $pdf->SetX($col2);
  $pdf->Box(0, 'Número da nota', sprintf('%06d', $nota->numerorps), 1, 'TBR', 'C', 10, ['helvetica', 'B', 10]);
  $pdf->SetX($col2);
  $pdf->Box(0, 'Data e hora da emissão', ymdDmyH($nota->dataemissaorps), 1, 'TBR', 'C', 10, ['helvetica', 'B', 9]);
  $pdf->SetX($col2);
  //$pdf->Box(0, 'Código de verificação', $nota->verificacao, 1, 'TBR', 'C', 10, ['helvetica', 'B', 10]);

  $pdf->Box(0, 'Código de verificação', '123', 1, 'TBR', 'C', 10, ['helvetica', 'B', 10]);

  //--------------------------------------
  // Grupos
  //--------------------------------------
  $pdf->fontCaption = ['helvetica', 'B', 6];
  $pdf->fontText = ['helvetica', '', 8];

  $yP = $pdf->GetY();
  $pdf->Box(0, '', "\nPRESTADOR DE SERVIÇOS", 1, 'BLR', 'C', 30, ['helvetica', 'B', 9], 'T');
  $yT = $pdf->GetY();
  $pdf->Box(0, '', "\nTOMADOR DE SERVIÇOS", 1, 'BLR', 'C', 30, ['helvetica', 'B', 9], 'T');
  $yD = $pdf->GetY();
  $pdf->Box(0, '', "\nDISCRIMINAÇÃO DOS SERVIÇOS", 1, 'BLR', 'C', 70, ['helvetica', 'B', 9], 'T');
  //$pdf->Box(0, '', 'VALOR TOTAL DA NOTA = R$ ' . numberFormat($nota->total), 1, 'BLR', 'C', 6, ['helvetica', 'B', 9]);
  $pdf->Box(0, '', 'VALOR TOTAL DA NOTA = R$ ' . '1000', 1, 'BLR', 'C', 6, ['helvetica', 'B', 9]);
  $pdf->Box(0, 'Código de atividade', '1401 - ajdksf akjsfdhkah', 1, 'BLR', 'L');
  $pdf->Box(38, 'Valor total das deduções', '0.00', 0, 'BLR', 'C', 8);
  //$pdf->Box(38, 'Base de cálculo (R$)', numberFormat($nota->total), 0, 'BLR', 'C', 8);
  $pdf->Box(38, 'Base de cálculo (R$)', '1000', 0, 'BLR', 'C', 8);
  $pdf->Box(18, 'Alíquota (%)', '0.00', 0, 'BLR', 'C', 8);
  $pdf->Box(38, 'Valor do ISS (R$)', '0.00', 0, 'BLR', 'C', 8);
  $pdf->Box(0, 'Crédito p/ abatimento do IPTU', '0',
    1, 'BLR', 'C', 8
  );
  $yO = $pdf->GetY();
  $pdf->Box(0, '', "\nOUTRAS INFORMAÇÕES", 1, 'BLR', 'C', 40, ['helvetica', 'B', 9], 'T');
  $pdf->SetFont('helvetica', '', 7);
  $pdf->SetY($yO+10);
  $pdf->MultiCell(0, 25, 
    'Esta NFS-e foi emitida com respaldo na Lei 73/2009. O crédito gerado estará disponível somente após o recolhimento ' .
    'do Simples Nacional, exceto para os casos previstos no 5º do Art. 10 da Lei 73/2009. Documento emitido por ME ou EPP ' .
    'optante pelo Simples Nacional. Não gera direito a crédito fiscal de IPI. ' .
    $nota->textTrib . ' ' . $nota->infoCompl ."\n",
    0, 'J', false, 1
  );
  $pdf->MultiCell(0, 0, 
    'Para verificar a autenticidade desta NFS-e acesse: https://stm.semfaz.saoluis.ma.gov.br/credenciamento/jsp/validacaonota/index.jsf',
    0, 'C', false, 1
  );

  //--------------------------------------
  // Prestador
  //--------------------------------------
  //$p = $emitente;
  //$endereco = "$p->logradouro, $p->numero $p->complemento - $p->bairro - $p->cep";
  //$pdf->Image(UPLOAD_PATH . $filial->logo, 25, $yP+2, 26, 26);
  $pdf->SetY($yP+10);
  $pdf->Columns([0 => 52, 5 => 100, 10 => 120, 15 => 150], [
    ['h' => 5, 0 => ['Razão social:', $nota->razaosocialprestador]], 
    ['h' => 5, 0 => ['CPF/CNPJ:', $nota->cpfcnpjtomador], 10 => ['Inscrição Municipal:', $nota->inscricaomunicipalprestador]],
    //['h' => 5, 0 => ['Endereço:', $endereco], 15 => ['Fone:', $p->telefone]],
    ['h' => 5, 0 => ['Endereço:', 'teste'], 15 => ['Fone:','teste']],
    // bug: na ultima linha o 'h' sempre dispara text overflow ...
    //[          0 => ['Município:', $emitMun->nome], 5 => ['UF:', $emitUF->sigla], 10 => ['Email:' , $p->email]]
    [          0 => ['Município:', 'São Luis'], 5 => ['UF:', 'MA'], 10 => ['Email:' , 'teste@t4ste']]
  ], ['helvetica', 'B', 8], null, 5);

  //--------------------------------------
  // Tomador
  //--------------------------------------
 // $p = $tomador;
  //$endereco = "$p->logradouro, $p->numero $p->complemento - $p->bairro - $p->cep";
  $pdf->SetY($yT+10);
  $pdf->Columns([0 => 25, 5 => 100, 10 => 120, 15 => 150], [
    [0 => ['Razão social:', $nota->razaosocialtomador]], 
    [0 => ['CPF/CNPJ:', $nota->cpfcnpjtomador], 10 => ['Inscrição Municipal:', $nota->inscricaomunicipaltomador]],
    [0 => ['Endereço:', $nota->tipologradourotomador], 15 => ['Fone:', '-']],
    [0 => ['Município:', $nota->municipioprestacaodescricao], 5 => ['UF:', 'MA'], 10 => ['Email:' , $nota->emailtomador]]
  ], ['helvetica', 'B', 8]);

  //--------------------------------------
  // Descriminação
  //--------------------------------------
  $pdf->SetY($yD+10);
  $pdf->Box(0, '', $nota->descricaorps, 1, 0, 'L', 70, ['helvetica', '', 8], 'T');

  // $txtPdf = $pdf->Output('nfse.pdf', 'S');
  // file_put_contents(UPLOAD_PATH . $target, $txtPdf);
  
  echo $pdf->Output('nfse.pdf', 'I');

    }

}
