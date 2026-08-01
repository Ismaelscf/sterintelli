<?php

namespace App\Http\Controllers;

use App\Repositories\NotaFiscalRepository;
use Illuminate\Http\Request;
use App\Services\ItauBoletoService;
use App\Repositories\NotaRepository;
use App\Model\Config;
use App\Model\Report;
use App\Model\Cliente;
use App\Repositories\BoletoItauRepository;

class ItauBoletoController extends Controller
{
    protected $itauBoletoService;
    protected $notaRepository;

    public function __construct(ItauBoletoService $itauBoletoService, NotaFiscalRepository $notaRepository)
    {
        $this->itauBoletoService = $itauBoletoService;
        $this->notaRepository = $notaRepository;
    }

    public function index ($numnota, $codcliente){
        $dadosNota = $this->notaRepository->buscarNF($numnota);
        $boletos = $this->itauBoletoService->consultarBoletosporNF($numnota);
        $nossoNumero = $this->itauBoletoService->getLastNumero();

        // dd($boletos);
        
        return view('boletos.index', compact('numnota','dadosNota', 'boletos', 'nossoNumero'));
    }

    function converterValor($valor) {
        $valorTratado = str_replace('.', '', number_format($valor, 2, '.', ''));
        
        $valorTratado = str_pad($valorTratado, 18, '0', STR_PAD_LEFT);
        
        return $valorTratado;
    }

    function converterPorcentagem($valor) {
        $valor = str_replace(',', '.', $valor);
    
        $valorFloat = floatval($valor);
    
        $valorFormatado = number_format($valorFloat, 5, '', '');
    
        $valorFinal = str_pad($valorFormatado, 12, '0', STR_PAD_LEFT);
    
        return $valorFinal;
    }

    public function gerarBoleto(Request $request)
    {
        $cliente = Cliente::where('CNPJ', $request->cnpjPagador)->first();
        // dd($request->all(), $cliente);

        if($cliente->bol_remover_impostos_nf == 'S'){
            // $valorBoleto = $this->converterValor($request->valorBoleto);
            $descontos = $request->pis + $request->cofins + $request->inss + $request->ir + $request->cll;
            if(isset($request->buttomIss)){
                $descontos = $descontos + $request->iss;
            }

            // dd($request->valorBoleto - $descontos);
            $novoValor = $request->valorBoleto - $descontos;
            $valorBoleto = $valorBoleto = $this->converterValor($novoValor);
            $valorDescontos = $this->converterValor(0);
        } else {
            $valorBoleto = $this->converterValor($request->valorBoleto);
            $descontos = $request->pis + $request->cofins + $request->inss + $request->ir + $request->cll;
            if(isset($request->buttomIss)){
                $descontos = $descontos + $request->iss;
            }
            $valorDescontos = $this->converterValor($descontos);
        }
        $dados = $request->all();
        // $valorBoleto = $this->converterValor($request->valorBoleto);
        // $descontos = $request->pis + $request->cofins + $request->inss + $request->ir + $request->cll;
        // if(isset($request->buttomIss)){
        //     $descontos = $descontos + $request->iss;
        // }
        // $valorDescontos = $this->converterValor($descontos);

        $emissao = date('Y-m-d');
        $dt_multa = date('Y-m-d', strtotime($request->dt_vencimento . ' +1 day'));


        if(isset($request->pf)){
            $codigo_tipo_pessoa = "F";
            $numero_cadastro_campo = 'numero_cadastro_pessoa_fisica';
            $cpfCnpj = str_pad($request->cnpjPagador, 11, '0', STR_PAD_LEFT);
        } else {
            $codigo_tipo_pessoa = "J";
            $numero_cadastro_campo = 'numero_cadastro_nacional_pessoa_juridica';
            $cpfCnpj = str_pad($request->cnpjPagador, 14, '0', STR_PAD_LEFT);
        }

        $emailPagador = $request->emailPagador;
        $juros = $this->converterPorcentagem($request->juros);
        $multa = $this->converterPorcentagem($request->multa);
        $cep = preg_replace('/[.\-\s]/', '', $request->cepPagador);

        if(isset($request->protestar)){
            $protesto = 'true';
            $day = $request->day;
        }else{
            $protesto = 'false';
            $day = 0;
        }

        if(isset($request->negativar)){
            if ($request->day >= 2 && $request->day <= 99) {
                $negativacao = [
                    "codigo_tipo_negativacao" => 2,
                    "quantidade_dias_negativacao" => $request->day
                ];
            } else {

                dd("A quantidade de dias para negativação deve ser entre 2 e 99.");
            }
        }else{
            $negativacao = [
                "codigo_tipo_negativacao" => "5"
            ];
        }
        $nomeCobrança = substr(preg_replace('/[^A-Za-z0-9 ]/', '', $request->razaoSocialPagador), 0, 50);
        $ruaPagador = substr(preg_replace('/[^A-Za-z0-9 ]/', '', $request->ruaPagador), 0, 50);
        $bairroPagador = substr(preg_replace('/[^A-Za-z0-9 ]/', '', $request->bairroPagador), 0, 50);
        $dadosBoleto = [
            "etapa_processo_boleto" => "efetivacao", 
            "codigo_canal_operacao" => "API",
            "beneficiario" => [
                "id_beneficiario" => "$request->id_beneficiario",
                "nome_cobranca" => "$request->razaoSocialBeneficiario", 
                "tipo_pessoa" => [
                    "codigo_tipo_pessoa" => "J",
                    "numero_cadastro_nacional_pessoa_juridica" => "$request->cnpjBeneficiario", 
                ],
                "endereco" => [
                    "nome_logradouro" => "$request->ruaBeneficiario",
                    "nome_bairro" => "$request->bairroBeneficiario",
                    "nome_cidade" => "$request->cidadeBeneficiario",
                    "sigla_UF" => "$request->estadoBeneficiario",
                    "numero_CEP" => "$request->cepBeneficiario"
                ]
            ],
            "dado_boleto" => [
                "descricao_instrumento_cobranca" => "boleto",
                "forma_envio" => "email",
                "texto_endereco_email" => "$emailPagador",
                "assunto_email" => "Boleto Steriliza",
                "mensagem_email" => "Segue, em anexo o Boleto da Striliza",
                "tipo_boleto" => "a vista",
                "codigo_carteira" => "109",
                "valor_titulo" => "$valorBoleto",
                "codigo_especie" => "96",
                "valor_abatimento" => "$valorDescontos",
                "data_emissao" => "$emissao",
                "pagamento_parcial" => false,
                "pagador" => [
                    "pessoa" => [
                        "nome_pessoa" => "$nomeCobrança",
                        "tipo_pessoa" => [
                            "codigo_tipo_pessoa" => "$codigo_tipo_pessoa",
                            "$numero_cadastro_campo" => "$cpfCnpj"
                        ]
                    ],
                    "endereco" => [
                        "nome_logradouro" => "$ruaPagador",
                        "nome_bairro" => "$bairroPagador",
                        "nome_cidade" => "$request->cidadePagador",
                        "sigla_UF" => "$request->estadoPagador",
                        "numero_CEP" => "$cep"
                    ],
                    "texto_endereco_email" => "$emailPagador"
                ],
                "dados_individuais_boleto" => [
                    [
                        "numero_nosso_numero"=> "$request->nosso_numero",
                        "data_vencimento" => "$request->dt_vencimento",
                        "valor_titulo" => "$valorBoleto",
                        "texto_seu_numero" => "$request->nf"
                    ]
                ],
                "multa" => [
                    "codigo_tipo_multa" => "02",
                    "percentual_multa" => "$multa"
                ],
                "recebimento_divergente" => [
                    "codigo_tipo_autorizacao" => "03"
                ],
                "protesto"=> [
                    "protesto"=> $protesto,
                    "quantidade_dias_protesto"=> "$day"
                ],
                "desconto_expresso" => false
            ]
        ];
            // Verificação da variável juros_ativacao
        if($request->valorBoleto > 15){
            $dadosBoleto['dado_boleto']['juros'] = [
                "codigo_tipo_juros" => "90",
                "percentual_juros" => $juros
            ];
        } else {
            $dadosBoleto['dado_boleto']['juros'] = [
                "codigo_tipo_juros" => "05"
            ];
        }
        // dd($dadosBoleto);
        // dd('segurança');
        try {
            $boleto = $this->itauBoletoService->emitirBoleto($dadosBoleto);
            $retorno =  response()->json($boleto);
            // dd($request->id_beneficiario, $request->nosso_numero, $request->numeronota);

            
            $save = $this->itauBoletoService->salvarDadosBoleto($request->id_beneficiario, $request->nosso_numero, $request->numeronota);

 
            return redirect()->back()->with('message', 'Dados do boleto salvos com sucesso.');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Erro ao salvar os dados do boleto: ' . $e->getMessage()]);
        }
    }

    public function consultarBoleto($nossoNumero)
    {
        try {
            $boleto = $this->itauBoletoService->consultarBoleto($nossoNumero);
            return $this->imprimirBoleto($boleto);    
            return response()->json($boleto);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function instruirBoleto($nossoNumero, Request $request)
    {
        try {
            $instrucoes = $request->all();
            $boleto = $this->itauBoletoService->instruirBoleto($nossoNumero, $instrucoes);
            return response()->json($boleto);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function posConsultarBoletosEmitidos(Request $request)
    {
        try {
            $filters = $request->all();
            $boletos = $this->itauBoletoService->consultarBoletosEmitidos($filters);
            return response()->json($boletos);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function imprimirBoleto($jsonRetorno) {
        // dd($jsonRetorno);
        $pdf = new Report(new Config('Boleto'));
        $pdf->SetMargins(11, 5, 20);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(False, 0);
        $pdf->AddPage();
        $pdf->SetCellPaddings(0, 0, 0, 0);
        $pdf->SetLineStyle(array('width' => 0.3));
    
        $fontCaption = ['helvetica', '', 7];
        $fontValor = ['helvetica', 'B', 7];

        $fontValorPequeno = ['helvetica', 'B', 5];
        $fontCaptionPequeno = ['helvetica', '', 5];
    
        // Mapear valores do JSON para variáveis
        $beneficiario = $jsonRetorno['data'][0]['beneficiario'];
        $dadoBoleto = $jsonRetorno['data'][0]['dado_boleto'];
        $pagador = $dadoBoleto['pagador'];
        $dadosIndividuaisBoleto = $dadoBoleto['dados_individuais_boleto'][0];
    
        $vencimento = $dadosIndividuaisBoleto['data_vencimento'];
        $valorDocumento = $dadosIndividuaisBoleto['valor_titulo'];
        $nomePagador = $pagador['pessoa']['nome_pessoa'];
        $nomeBeneficiario = $beneficiario['nome_cobranca'];
        $enderecoBeneficiario = $beneficiario['endereco']['nome_logradouro'] . ' ' . $beneficiario['endereco']['nome_bairro'];
        $agenciaBeneficiario = $beneficiario['id_beneficiario'];
        $nossoNumero = $dadosIndividuaisBoleto['numero_nosso_numero'];
        $numeroLinhaDigitavel = $dadosIndividuaisBoleto['numero_linha_digitavel'];
        $codigoBarras = $dadosIndividuaisBoleto['codigo_barras'];
        $cnpjBeneficiario = $beneficiario['tipo_pessoa']['numero_cadastro_nacional_pessoa_juridica'];
        $mensagensCobranca = $dadosIndividuaisBoleto['mensagens_cobranca'];

    
        // Verificar se a pessoa é física ou jurídica
        if ($pagador['pessoa']['tipo_pessoa']['codigo_tipo_pessoa'] === 'F') {
            $cpfCnpjPagador = $pagador['pessoa']['tipo_pessoa']['numero_cadastro_pessoa_fisica'];
        } else {
            $cpfCnpjPagador = $pagador['pessoa']['tipo_pessoa']['numero_cadastro_nacional_pessoa_juridica'];
        }
    
        //186
        $y = $pdf->GetY();
        $pdf->Box(165, '', 'Comprovante de Entrega', 0, '', 'R', 10, ['helvetica', 'B', 9]);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 10);
        $pdf->Image('images/logo.itau.jpg', 11.5, $y1 + 9, 28, 7);
        $pdf->Box(67, '', '', 0, 'B', 'C', 7);
        $pdf->Box(45, 'Vencimento', '   ' . $this->formataDataRetonoBoleto($vencimento), 0, 'LTB', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(74, 'Valor do documento', $this->formataDinheiro($valorDocumento), 0, 'LTB', 'R', 7, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 7);
        $pdf->Box(93, 'Pagador', '   ' . $nomePagador, 0, 'B', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(93, 'Beneficiário', '   ' . $nomeBeneficiario, 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 7);
        $pdf->Box(186, 'Endereço Beneficiário / Sacador Avalista', '   ' . $enderecoBeneficiario, 0, 'B', 'L', 7, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 7);
        $pdf->Box(60, 'Agência / Código Beneficiário', '   ' . $agenciaBeneficiario, 0, 'B', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(46, 'Nosso Número', '   ' . '109/' . substr($nossoNumero, 0, -1) . '-' . substr($nossoNumero, -1), 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(35, 'Nº Documento', '   ' . $dadosIndividuaisBoleto['texto_seu_numero'], 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(45, 'CNPJ', '   ' . $cnpjBeneficiario, 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 7);
        $pdf->Box(186, '', 'Para uso da Entregadora', 0, 'B', 'L', 15, ['helvetica', 'B', 9], 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 15);
        $pdf->Box(32, 'Data', '', 0, 'LRB', 'L', 9, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(65, 'Nome', '', 0, 'LB', 'L', 9, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(52, 'Assinatura recebedor', '', 0, 'LB', 'L', 9, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(37, 'Motivo da não entrega', '', 0, 'LR', 'L', 9, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 9);
        $pdf->Box(186, '', '', 0, 'LR', 'L', 6, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 5);
        $pdf->Box(5, '', '', 0, 'L', 'L', 5, $fontValor, 'B', false, $fontCaption);
        $pdf->SetLineStyle(array('dash' => '2'));
        $pdf->Box(10, '', '', 0, 'LT', 'L', 5, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(65, '[  ] Mudou-se', '', 0, 'T', 'T', 5, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(101, '[  ] Desconhecido', '', 0, 'TR', 'L', 5, $fontValor, 'B', false, $fontCaption);
        $pdf->SetLineStyle(array('dash' => 0));
        $pdf->Box(5, '', '', 0, 'R', 'L', 5, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 5);
        $pdf->Box(5, '', '', 0, 'L', 'L', 5, $fontValor, 'B', false, $fontCaption);
        $pdf->SetLineStyle(array('dash' => '2'));
        $pdf->Box(10, '', '', 0, 'L', 'L', 5, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(65, '[  ] End. Insuficiente', '', 0, '', 'T', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(101, '[  ] Falecido', '', 0, 'R', 'L', 5, $fontValor, 'B', false, $fontCaption);
        $pdf->SetLineStyle(array('dash' => 0));
        $pdf->Box(5, '', '', 0, 'R', 'L', 5, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 5);
        $pdf->Box(5, '', '', 0, 'L', 'L', 5, $fontValor, 'B', false, $fontCaption);
        $pdf->SetLineStyle(array('dash' => '2'));
        $pdf->Box(10, '', '', 0, 'L', 'L', 5, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(65, '[  ] Recusado', '', 0, '', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(101, '[  ] Outros _________', '', 0, 'R', 'L', 5, $fontValor, 'B', false, $fontCaption);
        $pdf->SetLineStyle(array('dash' => 0));
        $pdf->Box(5, '', '', 0, 'R', 'L', 5, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 5);
        $pdf->Box(5, '', '', 0, 'L', 'L', 5, $fontValor, 'B', false, $fontCaption);
        $pdf->SetLineStyle(array('dash' => '2'));
        $pdf->Box(10, '', '', 0, 'LB', 'L', 5, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(65, '[  ] Ausente', '', 0, 'B', 'T', 5, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(101, '', '', 0, 'BR', 'L', 5, $fontValor, 'B', false, $fontCaption);
        $pdf->SetLineStyle(array('dash' => 0));
        $pdf->Box(5, '', '', 0, 'R', 'L', 5, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 5);
        $pdf->Box(186, '', '', 0, 'LRB', 'L', 5, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 5);
        $pdf->SetLineStyle(array('dash' => '2'));
        $pdf->Box(186, '', '', 0, 'B', 'L', 40, $fontValor, 'B', false, $fontCaption);
    
        $pdf->SetLineStyle(array('dash' => 0));
    
        /** ---------------------------------------
         * fim da primeira parte
         * ----------------------------------------
         */
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 40);
        $pdf->Box(186, '', 'Autenticação mecânica', 0, '', 'C', 8, ['helvetica', '', 9], 'M', false, ['helvetica', '', 9], 'C');
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 8);
        $pdf->Box(186, '', 'Recibo do Pagador', 0, '', 'R', 8, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 9], 'C');
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 8);
        $pdf->Image('images/logo.itau.jpg', 11.5, $y1 + 7, 28, 7);
        $pdf->Box(60, '', '', 0, 'B', 'C', 7);
        $pdf->Box(46, 'Vencimento', '   ' . $this->formataDataRetonoBoleto($vencimento), 0, 'LTB', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(80, 'Valor do documento', $this->formataDinheiro($valorDocumento), 0, 'LTB', 'R', 7, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 7);
        $pdf->Box(93, 'Pagador', '   ' . $nomePagador, 0, 'B', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(93, 'Beneficiário', '   ' . $nomeBeneficiario, 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 7);
        $pdf->Box(186, 'Endereço Beneficiário / Sacador Avalista', '   ' . $enderecoBeneficiario, 0, 'B', 'L', 7, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 7);
        $pdf->Box(60, 'Agência / Código Beneficiário', '   ' . $agenciaBeneficiario, 0, 'B', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(46, 'Nosso Número', '   ' . '109/' . substr($nossoNumero, 0, -1) . '-' . substr($nossoNumero, -1), 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(35, 'Nº Documento', '   ' . $dadosIndividuaisBoleto['texto_seu_numero'], 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(45, 'CNPJ', '   ' . $cnpjBeneficiario, 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 6);
        $pdf->SetLineStyle(array('dash' => '2'));
        $pdf->Box(186, '', '', 0, 'B', 'L', 5, $fontValor, 'B', false, $fontCaption);
    
        $pdf->SetLineStyle(array('dash' => 0));
    
        /** ---------------------------------------
         * fim do topo do boleto
         * ----------------------------------------
         */
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 7);
        $pdf->Image('images/logo.itau.jpg', 11.5, $y1 + 6, 28, 7);
        $pdf->Box(45, '', '', 0, 'B', 'C', 7);
        $pdf->Box(20, '341-7', '', 0, 'LB', 'C', 7, ['helvetica', 'B', 16], 'B', false, ['helvetica', 'B', 15], 'C');
        $pdf->Box(121, '', $numeroLinhaDigitavel, 0, 'LB', 'C', 7, ['helvetica', '', 11], 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 7);
        $pdf->Box(141, 'Local de pagamento', '   ATÉ O VENCIMENTO, PREFERENCIAMENTO NO ITAÚ                                                                                                     APÓS O VENCIMENTO, SOMENTE NO ITAÚ', 0, 'B',
            'L', 10, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(45, 'Vencimento', $this->formataDataRetonoBoleto($vencimento), 0, 'LB',
            'R', 10, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 10);
        $pdf->Box(141, 'Beneficiário', '   ' . $nomeBeneficiario, 0, 'B', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(45, 'Agência / Código Beneficiário', '  ' . $agenciaBeneficiario, 0,
            'LB', 'R', 7, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 7);
        $pdf->Box(35, 'Data do documento', '   ' . $this->formataDataRetonoBoleto($dadosIndividuaisBoleto['data_vencimento']), 0, 'B', 'L', 7,
            $fontValor, 'B', false, $fontCaption);
        $pdf->Box(37, 'No Do documento', '   ' . $dadosIndividuaisBoleto['texto_seu_numero'], 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(21, 'Espécie doc.', '   ' . substr($dadoBoleto['descricao_especie'], 0, 3), 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(13, 'Aceite', '   ' . $dadoBoleto['codigo_aceite'], 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(35, 'Data Processamento', '   ' . $this->formataDataRetonoBoleto($dadosIndividuaisBoleto['data_vencimento']), 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(45, 'Nosso número', '109/' . substr($nossoNumero, 0, -1) . '-' . substr($nossoNumero, -1), 0, 'LB', 'R', 7, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 7);
        $pdf->Box(35, 'Uso do Banco', '', 0, 'B', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(24, 'Carteira', '   109', 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(13, 'Espécie', '   R$', 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(34, 'Quantidade', '', 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(35, 'Valor', '', 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(45, '(=) Valor do Documento', $this->formataDinheiro($valorDocumento), 0, 'LB', 'R', 7, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 7);
        $pdf->Box(141, 'Instruções de responsabiliadde do BENEFICIÁRIO. Qualquer dúvida sobre este boleto, contate o BENEFICIÁRIO.', '', 0,'', 'L', 5, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(45, '(-) Desconto/Abatimento', '', 0, 'LB', 'R',9, $fontValor, 'B', false, $fontCaption);
    
        foreach ($mensagensCobranca as $mensagem) {
            $y1 = $pdf->GetY();
            $pdf->SetY($y1 + 4);  
            $pdf->Box(141, $mensagem['mensagem'], '', 0, '', 'L', 5, $fontValor, 'B', false, $fontCaption);
            $pdf->Box(45, '', '', 0, 'L', 'R', 5, $fontValor, 'B', false, $fontCaption);
            
        }

        
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 1);
        $pdf->Box(141, '', '', 0, '', 'L', 9, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(45, '(+) Juros/Multa', '', 0, 'L', 'R', 9, $fontValor, 'B', false, $fontCaption);


        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 4); 
        $pdf->Box(141, 'CASO NAO CONSIGA PAGAR ATÉ O VENCIMENTO', '', 0, '', 'L', 5, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(45, '', '', 0, 'LB', 'R', 5, $fontValor, 'B', false, $fontCaption);
        
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 4); 
        $pdf->Box(141, 'ENTRAR EM CONTATO COM A EMPRESA, POIS O', '', 0, '', 'L', 5, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(45, '', '', 0, 'L', 'R', 5, $fontValor, 'B', false, $fontCaption);
        
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 4); 
        $pdf->Box(141, 'BOLETO ESTÁ NEGATIVADO APÓS 5 DIAS.', '', 0, '', 'L', 5, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(45, '(=) Valor Cobrado', '', 0, 'LB', 'R', 8, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(45, '', '', 0, '', 'R', 0, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 +4);
        $pdf->Box(141, '', '', 0,
            'RB', 'L', 0, $fontValor, 'B', false, $fontCaption);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 7);
        $pdf->Box(25, 'Pagador: ', '', 0, '', 'L', 4, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(101, $nomePagador, '', 0, '', 'L', 4, $fontValor, 'B', false, ['helvetica', 'B', 7]);
        $pdf->Box(60, 'CNPJ: ' . $cpfCnpjPagador, '', 0, '', 'L', 4, $fontValor, 'B', false, ['helvetica', 'B', 7]);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 5);
        $pdf->Box(25, 'Endereço: ', '', 0, '', 'L', 4, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(161, $pagador['endereco']['nome_logradouro'] . ' ' . $pagador['endereco']['nome_bairro'] . ' ' . $pagador['endereco']['nome_cidade'] . '-' . $pagador['endereco']['sigla_UF'], '', 0,
            '', 'L', 4, $fontValor, 'B', false, ['helvetica', 'B', 7]);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 5);
        $pdf->Box(25, 'Sacador/Avalista: ', '', 0, 'B', 'L', 4, $fontValor, 'B', false, $fontCaption);
        $pdf->Box(161, '', '', 0, 'B', 'L', 4, $fontValor, 'B', false, $fontCaption);
    
        $style = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false,
            'text' => false, // Desativa a exibição dos números abaixo do código de barras
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 0 // Garantir que o texto não seja esticado
        );
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 6);
        $pdf->write1DBarcode($codigoBarras, 'I25', '', '', '', 18, 0.4, $style, 'N');
    
        $pdf->SetY($y1 + 5);
        $pdf->Box(125, '', '', 0, '', 'C', 5);
        $pdf->Box(61, '', 'Ficha de Compensação', 0, '', 'R', 5, ['helvetica', 'B', 9], 'B', false, ['helvetica', 'B', 9]);
    
        $y1 = $pdf->GetY();
        $pdf->SetY($y1 + 5);
        $pdf->Box(125, '', '', 0, '', 'C', 5);
        $pdf->Box(61, '', 'Autenticação Mecânica', 0, '', 'R', 5, ['helvetica', '', 7], 'B', false, ['helvetica', 'B', 11]);
    
        echo $pdf->Output('boleto.pdf', 'I');
    }

    public function formataDataRetonoBoleto($data) {
        return date('d/m/Y', strtotime($data));
    }

    public function formataDinheiro($valor, $prefixo = false) {
        $valorFormatado = number_format($valor, 2, ',', '.');
        return $prefixo ? 'R$ ' . $valorFormatado : $valorFormatado;
    }

    public function alterarVencimento(Request $request)
    {

        try {
            $boleto = $this->itauBoletoService->consultarBoleto($request->nosso_numero);
            
            // Verificar se o boleto foi encontrado
            if (isset($boleto['data'][0]['id_boleto'])) {
                $boletoData = $boleto['data'][0];
                $idBoleto = $this->montarIdBoleto($boletoData);
    
                // Alterar a data de vencimento do boleto
                $boletoAlterado = $this->itauBoletoService->alterarVencimento($idBoleto, $request->data_vencimento);
    
                if ($boletoAlterado) {
                    return redirect()->back()->with('message', 'Data de vencimento alterada com sucesso.');
                } else {
                    return redirect()->back()->with('error', 'Erro ao alterar a data de vencimento.');
                }
            } else {
                return redirect()->back()->with('error', 'Boleto não encontrado.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    
    function montarIdBoleto($boletoData)
    {
        $codigoAgencia = substr($boletoData['beneficiario']['id_beneficiario'], 0, 4);
        $codigoContaCorrente = substr($boletoData['beneficiario']['id_beneficiario'], 4, 7);
        $digitoVerificador = substr($boletoData['beneficiario']['id_beneficiario'], -1);
        $codigoCarteira = $boletoData['dado_boleto']['codigo_carteira'];
        $nossoNumero = $boletoData['dado_boleto']['dados_individuais_boleto'][0]['numero_nosso_numero'];

        $idBoleto = $codigoAgencia . $codigoContaCorrente . $digitoVerificador . $codigoCarteira . $nossoNumero;

        return $idBoleto;
    }

    public function ajaxBoletos($numnota)
    {
        $boletos = $this->itauBoletoService->consultarBoletosporNF($numnota);

        return view('boletos.partials.list', compact('boletos'));
    }

}
