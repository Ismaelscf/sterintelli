<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use App\Services\FocusNfeService;
use App\Services\CnabRetornoParser;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use stdClass;
use App\Repositories\NotaRepository;

use App\Mail\SendMailUser;
use App\Model\Cobranca;

use Illuminate\Support\Facades\DB;

class NotaController extends Controller
{
    
    protected $repository;
    protected $ambiente;
    protected $dadosEmissor;
    protected $focusNfe;


    public function __construct(stdClass $rps)
    {

      //$this->middleware('auth');

      $this->repository = new NotaRepository();

      parent::__construct();

      //1-producao, 2-homologacao
      $this->ambiente = 1;
      $this->dadosEmissor = $this->repository->buscaDadosEmissor($this->ambiente);

      $this->focusNfe = new FocusNfeService();

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dados = json_decode(json_encode($this->repository->buscaDadosIniciais()));

        // if (env('APP_ENV') == 'production') {
        //     $atrasos =  $this->repository->consultar_notas_vencidas();
        //     if(!empty($atrasos)){
        //         $envio = $this->rotina_email_cobranca($atrasos);
        //     }
        // }


        //mensagens vindas de um redirect (ex: apos emitir nota) somam com as do proprio
        //request atual; a sessao flash so dura ate o proximo request, entao um F5 nessa
        //tela nao reexibe a mensagem nem reenvia nada (ja e um GET simples)
        $msgInforma = array_merge($this->msgInforma, session('msgInforma', []));
        return view('notas.index', compact('dados', 'msgInforma'));

    }

    public function rotina_email_cobranca($atrasos) {
        require __DIR__ . '/../../../vendor/autoload.php';

        $mailConfig = array(
            'host' => 'email-ssl.com.br',
            'port' => 465,
            'username' => 'nao-responda@steriliza.com.br',
            'password' => 'Ste@2020',
            'from' => 'nao-responda@steriliza.com.br'
        );
    
        foreach ($atrasos as $dados2) {

            $numeroNota = $dados2->numeronota;
            $email = $dados2->email;
            $subject = "NOTA EM ABERTO BRITO & SOARES LTDA/ STERILIZA";
            $valorFormatado = 'R$ ' . number_format($dados2->valornota, 2, ',', '.');
            $dataVencimento = $dados2->datavencimento;
    
            $msg = "Bom dia,\n\nEspero que esteja tudo bem.\n\nEstou entrando em contato em relação a um débito pendente de sua empresa conosco. De acordo com as novas diretrizes da nossa empresa, essa pendência já deveria ter sido encaminhada para protesto. No entanto, em consideração à nossa parceria, aguardarei sua resposta até o final do dia de hoje.\n\nReferente à nota fiscal nº $numeroNota, com data de vencimento em $dataVencimento, no valor de $valorFormatado. Caso precise de mais informações, de um boleto atualizado, ou se estiver encontrando qualquer dificuldade para efetuar o pagamento, por favor, entre em contato com nosso setor financeiro. Se o pagamento já foi realizado, pedimos que envie os comprovantes bancários para evitarmos que o título seja protestado indevidamente.\n\nEstou à disposição para responder pelo e-mail abaixo ou pelo WhatsApp.\n\nAtenciosamente,\n\nFernando Belfort Filho\nGERENTE ADMINISTRATIVO FINANCEIRO\nBrito & Soares Ltda / Steriliza\nRua dos Flamingos, Qd XV, Número 07, Parque Atlântico, Olho D’água\nSão Luis - MA\nCEP: 65066-060\nTel: (98) 3248-3379/3248-5544/98116-1502 (WhatsApp)\nEmail: fernando@steriliza.com.br";

    
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host = $mailConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Port = $mailConfig['port'];
            $mail->Username = $mailConfig['username'];
            $mail->Password = $mailConfig['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($mailConfig['from'], 'Steriliza');
            $mail->addAddress($email);
            // $mail->addCC('fernando@steriliza.com.br');
            // $mail->addCC('financeiro@steriliza.com.br');
            $mail->Subject = $subject;
            $mail->Body = $msg;
    
    
            if ($mail->send()) {
                $sucesso =  $this->repository->atualizar_status_envio_cobranca($dados2, 'S');
                $salvar = $this->repository->salvar_envio_cobranca($dados2);
            } else {
                $erro =  $this->repository->atualizar_status_envio_cobranca($dados2, 'E');
            }
        }
    
        return true;
    }
    
    public function cobranca($codigoCliente = null) {
        // if (env('APP_ENV') == 'production') {
        //     $atrasos =  $this->repository->consultar_notas_vencidas();
        //     if(!empty($atrasos)){
        //         $envio = $this->rotina_email_cobranca($atrasos);
        //     }
        // }

        $query = Cobranca::select(
            'CODIGO_CLIENTE',
            'NOME_CLIENTE',
            DB::raw('COUNT(DISTINCT NF) as numero_nf'),
            DB::raw('COUNT(*) as numero_emails_enviados')
        )
        ->groupBy('CODIGO_CLIENTE', 'NOME_CLIENTE');
    
        if ($codigoCliente) {
            $query->where('CODIGO_CLIENTE', $codigoCliente);
        }
    
        $emails = $query->get();
        // dd($emails);
        return view('cobranca.index', compact('emails'));
    }
    
    public function detalhesEmail($codigo){
        // if (env('APP_ENV') == 'production') {
        //     $atrasos =  $this->repository->consultar_notas_vencidas();
        //     if(!empty($atrasos)){
        //         $envio = $this->rotina_email_cobranca($atrasos);
        //     }
        // }
        
        $emails = Cobranca::where('CODIGO_CLIENTE', $codigo)->orderby('DATA_ENVIO', 'desc')->get();
        
        return view('cobranca.detalhes', compact('emails'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function preEmitir($idcliente, Request $request)
    {
        // dd('aqui');
        $dtInicial = $request->query->has('dtini') ? 
                          $request->query->all()['dtini'] : null;
        $dtFinal = $request->query->has('dtfim') ? 
                          $request->query->all()['dtfim'] : null;

        $searchOption = $request->query->has('searchOption') ? 
                          $request->query->all()['searchOption'] : null;
        
        $tipoEste = $request->query->has('tipoEste') ? 
                          $request->query->all()['tipoEste'] : null;



        $dadosEmissor = $this->dadosEmissor;
        $msgAlerta = [];

        //a Focus NFe controla a numeracao da RPS/NFSe internamente; este numero
        //e apenas um identificador local de exibicao, nao e enviado a Focus.
        $numRps = (int) date('YmdHis');
        $horaNota = date("H:i:s");


        $estados = $this->repository->consultarEstadosCod();
        $municipios = $this->repository->consultarMunicipiosCod();


        if ($dtInicial && $dtFinal){

                $notasPeriodo = $this->repository->buscaNotaEmitida($idcliente, 
                                $dtInicial, $dtFinal);

                
                if ($this->repository->count > 0){
                    
                array_push($msgAlerta, 'Existem nota(s) emitidas para esse período:' );

                foreach ($notasPeriodo as $nota) {

                    $msg =  'NumNota: '. $nota->NUMERONOTA.
                        ' <br>  Dt. Nota: '.$nota->DTANOTA.'  '.
                        ' Valor: '. $nota->VALORNOTA;

                    if($nota->NUMERO_NFSE)
                        $msg .=' <br>  NFSE: '. $nota->NUMERO_NFSE.'  '.
                        '   Código Verificação:'.$nota->CODIGOVERIFICACAO.
                        ' <br>link: <a href="sterintelli/public/notas/consultarnfse/'.$nota->NUMERO_NFSE.'/'.$nota->CODIGOVERIFICACAO.'/"> Acesse</a>';

                    else
                        $msg .= "<br> Sem NFSE cadastrada.";

                    array_push($msgAlerta, $msg);
                }

                }
                
                /*buscar os dados do período*/
                $dadosEmissao = $this->repository->buscaDadosEmissao(
                    $idcliente, $dtInicial, $dtFinal, $searchOption, $tipoEste
                );

                $dadosEmissao = (array) $dadosEmissao;
                $dadosEmissao = array_map('trim', $dadosEmissao);
                $dadosEmissao = (object) $dadosEmissao;
                                    
                // dd('aqui 6', $dadosEmissao);

                return view('notas.pre-emitir', 
                            compact('dadosEmissor', 'dadosEmissao', 
                            'dtInicial', 'dtFinal', 'numRps',
                            'horaNota', 'idcliente',
                            'msgAlerta', 'estados', 'municipios'
                            ));
        }else{


            /*buscar os dados do período*/
            $dadosEmissao = $this->repository->buscaDadosEmissaoEmBranco(
                $idcliente, $dtInicial, $dtFinal, $searchOption, $tipoEste
            );

            return view('notas.pre-emitir', 
            compact('dadosEmissor', 'dadosEmissao', 
            'dtInicial', 'dtFinal', 'numRps',
            'horaNota', 'idcliente',
            'msgAlerta', 'estados', 'municipios'
            ));

        }
            

    }

    public function preConsultarNotasEmitidas()
    {
        $clientes = $this->repository->consultarClientesCompleto();

        $msgInforma = $this->msgInforma;
        return view('notas.pre-consnotasemitidas', compact('clientes', 'msgInforma'));
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

            $descricaoRPS = str_replace('>', '', $request->descricaorps);
            $descricaoRPS = str_replace('<', '', $descricaoRPS);
            $descricaoRPS = str_replace('&', 'E', $descricaoRPS);
            $descricaoRPS = str_replace('"', '´', $descricaoRPS);
            $descricaoRPS = str_replace('\\', '/', $descricaoRPS);

            $dataEmissao = $request->has('dataemissaorps') ? $request->dataemissaorps : date('d/m/Y');
            $horaEmissao = $request->has('horaemissaorps') ? $request->horaemissaorps : '00:00:00';
            $dataEmissaoIso = $this->formataDataHoraEnvio($dataEmissao, $horaEmissao);

            $vTotServ = $this->formataValor($request->has('vTotServ') ? $request->vTotServ : 0.00);
            $vTotDeduc = $this->formataValor($request->has('vTotDeduc') ? $request->vTotDeduc : 0.00);

            //cada imposto so e calculado/enviado se o respectivo checkbox do formulario
            //estiver marcado; desmarcado = nao envia o campo para a Focus NFe
            $chkPis = $request->chkPis == true;
            $chkCofins = $request->chkCofins == true;
            $chkInss = $request->chkInss == true;
            $chkIr = $request->chkIr == true;
            $chkCsll = $request->chkCsll == true;

            $valorpis = $chkPis ? $this->formataValor($request->valorpis) : 0;
            $valorcofins = $chkCofins ? $this->formataValor($request->valorcofins) : 0;
            $valorinss = $chkInss ? $this->formataValor($request->valorinss) : 0;
            $valorir = $chkIr ? $this->formataValor($request->valorir) : 0;
            $valorcsll = $chkCsll ? $this->formataValor($request->valorcsll) : 0;

            //a NFSe Nacional agrupa a retencao de PIS/COFINS/CSLL num unico campo (vRetCSLL) e
            //exige um codigo indicando qual combinacao dos tres esta retida; mapeamento conforme
            //doc da Focus NFe (campos.focusnfe.com.br/nfse_nacional) para tipo_retencao_pis_cofins
            if ($chkPis && $chkCofins && $chkCsll) {
                $tipoRetencaoPisCofins = 3;
            } elseif ($chkCofins && $chkCsll) {
                $tipoRetencaoPisCofins = 7;
            } elseif ($chkPis && $chkCsll) {
                $tipoRetencaoPisCofins = 9;
            } elseif ($chkPis && $chkCofins) {
                $tipoRetencaoPisCofins = 4;
            } elseif ($chkCsll) {
                $tipoRetencaoPisCofins = 8;
            } elseif ($chkCofins) {
                $tipoRetencaoPisCofins = 6;
            } elseif ($chkPis) {
                $tipoRetencaoPisCofins = 5;
            } else {
                $tipoRetencaoPisCofins = 0; //PIS/COFINS/CSLL nao retidos
            }

            $aliquotaAtividade = $this->formataValor($request->has('aliquotaatividade') ? $request->aliquotaatividade : 0.00);

            //codigo IBGE do municipio do prestador (sempre Sao Luis - MA, fixo);
            //o servico e sempre prestado no proprio estabelecimento do prestador
            $codIbgePrestador = config('services.focusnfe.codigo_municipio_prestador', '2111300');

            //o formulario envia o COD_SIAFI do municipio do tomador; a Focus NFe exige o codigo IBGE
            $codSiafiTomador = $request->has('cidadetomador') ? $request->cidadetomador : '';
            $codIbgeTomador = $this->repository->buscaIbgePorSiafi($codSiafiTomador);

            if (empty($codIbgeTomador)) {
                return redirect()->back()->withErrors(['Não foi possível identificar o código IBGE do município do tomador (código SIAFI recebido: ' . $codSiafiTomador . '). Verifique o município selecionado no formulário.']);
            }

            $cpfCnpjTomador = preg_replace('/\D/', '', $request->has('cpfcnpjtomador') ? $request->cpfcnpjtomador : '');

            $valorIss = round($vTotServ * ($aliquotaAtividade / 100), 2);

            //codigo de tributacao nacional (NFSe Nacional) para "Esterilizacao, desinfeccao,
            //desinsetizacao, imunizacao, higienizacao, limpeza e congeneres" (LC 116 item 4.14).
            //Validado contra a Focus NFe homologacao (endpoint /v2/nfsen); os codigos antigos da
            //prefeitura (codigoservico "0709", codigoatividade "812900000") nao sao compativeis
            //com a lista nacional e NAO devem ser reutilizados aqui.
            $codigoTributacaoNacional = config('services.focusnfe.codigo_servico_nacional', '070901');
            $codigoNbs = config('services.focusnfe.codigo_nbs', '123019900');
            $codigoIndicadorOperacao = config('services.focusnfe.codigo_indicador_operacao', '030101');
            $ibsCbsSituacaoTributaria = config('services.focusnfe.ibs_cbs_situacao_tributaria', '200');
            $ibsCbsClassificacaoTributaria = config('services.focusnfe.ibs_cbs_classificacao_tributaria', '200029');

            //numero_dps precisa ser unico por prestador+serie (equivalente ao antigo numero de RPS,
            //que antes vinha da prefeitura via consultarSequencialRps). A Focus NFe nao gera isso
            //sozinha, entao usamos o timestamp como sequencial local.
            $numeroDps = time();

            $payload = [
                'data_emissao' => $dataEmissaoIso . '-03:00',
                'serie_dps' => 1,
                'numero_dps' => $numeroDps,
                'data_competencia' => substr($dataEmissaoIso, 0, 10),
                'emitente_dps' => 1,
                'codigo_municipio_emissora' => $codIbgePrestador,
                'cnpj_prestador' => preg_replace('/\D/', '', $this->dadosEmissor->CNPJ),
                'inscricao_municipal_prestador' => $this->dadosEmissor->IM,
                'codigo_opcao_simples_nacional' => 1, //1 = Nao optante (empresa e Lucro Real)
                'regime_especial_tributacao' => 0, //0 = Nenhum
                'razao_social_tomador' => $request->has('razaosocialtomador') ? $request->razaosocialtomador : '',
                'email_tomador' => $request->has('emailtomador') && $request->emailtomador != '' ? $request->emailtomador : null,
                'logradouro_tomador' => $request->has('logradourotomador') ? $request->logradourotomador : '',
                'numero_tomador' => $request->has('numeroenderecotomador') && $request->numeroenderecotomador != '' ? $request->numeroenderecotomador : 'S/N',
                'bairro_tomador' => $request->has('bairrotomador') ? $request->bairrotomador : '',
                'codigo_municipio_tomador' => $codIbgeTomador,
                'uf_tomador' => $request->has('cmbEstadoTomador') ? $request->cmbEstadoTomador : '',
                'cep_tomador' => preg_replace('/\D/', '', $request->has('ceptomador') ? $request->ceptomador : ''),
                'codigo_municipio_prestacao' => $codIbgePrestador,
                'codigo_tributacao_nacional_iss' => $codigoTributacaoNacional,
                //finalidade da emissao (tag finNFSe), obrigatoria no schema (xsd) antes do
                //bloco de codigo_indicador_operacao; 0 = NFS-e regular
                'finalidade_emissao' => 0,
                'codigo_nbs' => $codigoNbs,
                'codigo_indicador_operacao' => $codigoIndicadorOperacao,
                //indicador do destinatario (tag indDest), obrigatorio no schema; 0 = o
                //destinatario e o proprio tomador identificado na NFS-e (sempre o nosso caso,
                //nao ha destinatario separado do tomador)
                'indicador_destinatario' => 0,
                'descricao_servico' => $descricaoRPS,
                'valor_servico' => $vTotServ,
                'tributacao_iss' => 1, //1 = Operacao tributavel (nao e imunidade/exportacao/nao incidencia)
                'tipo_retencao_iss' => $request->chkIss == true ? 2 : 1, //1 = Nao retido, 2 = Retido pelo tomador
                //CST/cClassTrib do IBS/CBS, obrigatorios pelo schema da Reforma Tributaria
                'ibs_cbs_situacao_tributaria' => $ibsCbsSituacaoTributaria,
                'ibs_cbs_classificacao_tributaria' => $ibsCbsClassificacaoTributaria,
                //CST do PIS/COFINS: obrigatorio pelo schema (xsd) sempre que o bloco de
                //tributacao federal e enviado (mesmo com tipo_retencao_pis_cofins = 0);
                //01 = Operacao Tributavel com Aliquota Basica, cenario padrao da empresa
                'situacao_tributaria_pis_cofins' => '01',
                'tipo_retencao_pis_cofins' => $tipoRetencaoPisCofins,
                //Lei da Transparencia Fiscal (12.741/2012): tributos aproximados embutidos no preco
                'valor_total_tributos_federais' => $valorpis + $valorcofins + $valorinss + $valorir + $valorcsll,
                'valor_total_tributos_estaduais' => 0,
                'valor_total_tributos_municipais' => $valorIss,
            ];

            if (strlen($cpfCnpjTomador) > 11) {
                $payload['cnpj_tomador'] = $cpfCnpjTomador;
            } elseif (strlen($cpfCnpjTomador) > 0) {
                $payload['cpf_tomador'] = $cpfCnpjTomador;
            }

            //campos discriminados de tributacao federal, cada um so enviado se o respectivo
            //checkbox do formulario estiver marcado

            //debito de apuracao propria do PIS/COFINS (campos "PIS/COFINS - Debito Apuracao
            //Propria" na NFS-e); distinto do valor_csll abaixo, que e a retencao agrupada
            //PIS+COFINS+CSLL feita pelo tomador
            if ($chkPis) {
                $payload['valor_pis'] = $valorpis;
            }

            if ($chkCofins) {
                $payload['valor_cofins'] = $valorcofins;
            }

            if ($chkPis || $chkCofins || $chkCsll) {
                //PIS, COFINS e CSLL retidos sao unificados na tag vRetCSLL (campo valor_csll)
                $payload['valor_csll'] = $valorpis + $valorcofins + $valorcsll;
            }

            if ($chkIr) {
                $payload['valor_irrf'] = $valorir;
            }

            if ($chkInss) {
                $payload['valor_cp'] = $valorinss;
            }

            //codigo curto (STER000001) que vincula a nota ao(s) boleto(s) dela: enviado
            //como ref a Focus NFe, usado como NUMERONOTA enquanto a nota fica
            //"processando_autorizacao", e reaproveitado como "seu numero" na geracao do
            //boleto (ItauBoletoController::gerarBoleto) - substitui o antigo
            //'STE'.idcliente.'-'.timestamp, que passava de 20 caracteres e vinha cortado
            //no arquivo de retorno do banco (CNAB 400 so tem 10 posicoes pra esse campo)
            $ref = $this->repository->proximoCodigoVinculo();
            $lote = date('Ymd');

            //salva arquivo de log para depuracao
            $arqNomeEnv = 'nfe_emitidas/' . $lote . '/' . $request->idcliente . '_envio.json';
            $arqNomeRet = 'nfe_emitidas/' . $lote . '/' . $request->idcliente . '_retorno.json';
            $dirname = dirname($arqNomeRet);
            if (!is_dir($dirname))
                mkdir($dirname, 0755, true);

            $fp = fopen($arqNomeEnv, 'w');
            fwrite($fp, json_encode($payload));
            fclose($fp);

            $resposta = $this->focusNfe->emitir($ref, $payload);

            //a Focus NFe processa a emissao de forma assincrona; aguarda algumas
            //tentativas antes de responder ao usuario
            $tentativas = 0;
            while (
                isset($resposta['body']['status']) &&
                $resposta['body']['status'] == 'processando_autorizacao' &&
                $tentativas < 8
            ) {
                sleep(2);
                $resposta = $this->focusNfe->consultar($ref);
                $tentativas++;
            }

            $fp = fopen($arqNomeRet, 'w');
            fwrite($fp, json_encode($resposta));
            fclose($fp);

            $corpo = $resposta['body'];

            if (!isset($corpo['status']) || $corpo['status'] == 'erro_autorizacao') {
                $erros = [];
                foreach (($corpo['erros'] ?? []) as $erro) {
                    $erros[] = (isset($erro['codigo']) ? $erro['codigo'] . ' - ' : '') . ($erro['mensagem'] ?? json_encode($erro));
                }
                if (empty($erros)) {
                    $erros[] = 'Erro ao emitir NFSe na Focus NFe: ' . json_encode($corpo);
                }
                return redirect()->back()->withErrors($erros);
            }

            //prefixo "NAC" evita colisao com a PK_NOTAS: a numeracao da NFSe Nacional
            //reinicia do 1 por prestador, e provavelmente ja existe numeronota "1", "2"...
            //nos registros antigos emitidos via prefeitura (DSF)
            $numeroNota = $corpo['status'] == 'autorizado' ? 'NAC' . $corpo['numero'] : $ref;

            $dadosNota = array(
                'idcliente' => $request->idcliente,
                'numeroNota' => $numeroNota,
                'valorNota' => $vTotServ,
                'dtIni' => $request->dtInicial,
                'dtFim' => $request->dtFinal,
                'status' => 0,
                'dataemissaorps' => $dataEmissao,
                'percIss' => $aliquotaAtividade,
                'numeroNFe' => $corpo['numero'] ?? 0,
                'codigoVerificacao' => $corpo['codigo_verificacao'] ?? '',
                'chaveNfse' => json_encode($corpo),
                'descricaoNota' => $descricaoRPS,
                'dtVencimento' => $request->datavencimento,
                'valorpis' => $valorpis,
                'valorcofins' => $valorcofins,
                'valorinss' => $valorinss,
                'valorir' => $valorir,
                'valorcsll' => $valorcsll,
                'estadoTomador' => $request->cmbEstadoTomador,
                'municipioIdTomador' => $request->cidadetomador,
                'percIR' => $request->has('aliquotair') ? $this->formataValor($request->aliquotair) : 0,
                'refFocus' => $ref,
                'statusFocus' => $corpo['status'],
                'urlDanfse' => $corpo['url_danfse'] ?? '',
                'caminhoXml' => $corpo['caminho_xml_nota_fiscal'] ?? '',
            );

            $retornoSalvar = $this->repository->salvaNotaEmitida($dadosNota);

            if ($retornoSalvar[0] == false) {

                $erros = [];
                array_push($erros, 'A NOTA FOI ENVIADA - ERRO AO SALVAR NO BANCO DE DADOS.');
                array_push($erros, 'ANTES DE FAZER NOVA EMISSÃO ENTRE EM CONTATO COM O SUPORTE.');
                array_push($erros, $retornoSalvar[1]);

                return redirect()->back()->withErrors($erros);
            }

            //retorna a página inicial
            if ($corpo['status'] == 'autorizado') {
                array_push($this->msgInforma, 'Nota emitida com sucesso.');
                array_push($this->msgInforma, 'NFSe: ' . $corpo['numero'] .
                    ' Código de Verificação:' . $corpo['codigo_verificacao']);
                array_push($this->msgInforma, 'Para imprimir <a href="' . url('notas/imprimirnfse/' . $numeroNota . '/' . $corpo['codigo_verificacao'] . '/') . '" target="_blank" class="text-danger"><b>clique aqui</b></a>');
            } else {
                array_push($this->msgInforma, 'Nota enviada e está em processamento na prefeitura (referência: ' . $ref . '). Consulte novamente em instantes.');
            }

        } catch (\Exception $e) {
            return redirect()->back()->withErrors([$e->getMessage()]);
        }

        //redirect (Post/Redirect/Get) em vez de renderizar a index() direto: assim a URL do
        //navegador vira um GET (/notas), e um F5 na tela de sucesso so recarrega a pagina em
        //vez de reenviar o POST de emissao (o que geraria uma NFSe duplicada na Focus NFe)
        return redirect()->route('notas.index')->with('msgInforma', $this->msgInforma);

    }

    public function preConsultarNfse()
    {
        return view('notas.pre-consultarnfse');
    }


    //aceita GET (redirect apos cancelar, com dtIni/dtFim na query string) e POST
    //(submissao normal do formulario de busca) - ver rota. $request->dtIni le dos dois
    //formatos igual, entao o resto do metodo nao precisa distinguir
    public function posConsultarNfse(Request $request)
    {

        //a Focus NFe so permite consultar por referencia (nao por periodo),
        //entao a busca por periodo usa os registros ja gravados localmente. Traz todos
        //os campos uteis de uma vez (consultarNotasEmitidas), sem filtrar nada aqui -
        //a view e que decide o que mostrar
        $dtIni = $request->dtIni;
        $dtFim = $request->dtFim;

        $notas = $this->repository->consultarNotasEmitidas($dtIni, $dtFim, 0);

        if (empty($notas)) {
            return redirect()->back()->withErrors(['Não foram encontradas NFSes no período.']);
        }

        //mensagens vindas de um redirect (ex: apos cancelar notas) somam com as do
        //proprio request atual, mesmo padrao usado em index()
        $msgInforma = array_merge($this->msgInforma, session('msgInforma', []));

        return view('notas.pos-consultarnfse', compact('notas', 'dtIni', 'dtFim', 'msgInforma'));
    }

    public function preBaixaRetorno()
    {
        return view('notas.pre-baixa');
    }

    //recebe um ou mais arquivos .RET (CNAB 400), parseia e mostra uma tela de
    //conferencia antes de gravar qualquer coisa no banco; nada e alterado aqui
    public function posBaixaRetorno(Request $request)
    {
        if (!$request->hasFile('arquivos')) {
            return redirect()->back()->withErrors(['Selecione ao menos um arquivo .RET.']);
        }

        $parser = new CnabRetornoParser();
        $linhas = [];

        foreach ($request->file('arquivos') as $arquivo) {
            $conteudo = file_get_contents($arquivo->getRealPath());
            $registros = $parser->parse($conteudo, $arquivo->getClientOriginalName());

            foreach ($registros as $registro) {
                $candidatos = $this->repository->buscaNotasPorNumeroOuPrefixo($registro['numeronota']);

                $registro['encontrado'] = count($candidatos) === 1;
                $registro['ambiguo'] = count($candidatos) > 1;

                if ($registro['encontrado']) {
                    $notaAtual = $candidatos[0];
                    //numeronota real do banco, que pode ser mais longo que o campo
                    //truncado de 10 posicoes vindo do CNAB (ver buscaNotasPorNumeroOuPrefixo);
                    //e essa versao completa que deve ser usada pra gravar a baixa
                    $registro['numeronota_real'] = $notaAtual->NUMERONOTA;
                    $registro['truncado'] = $notaAtual->NUMERONOTA !== $registro['numeronota'];
                    $registro['ja_tinha_pagamento'] = !empty($notaAtual->DTAPAGO);
                    $registro['dtapago_atual'] = $notaAtual->DTAPAGO;
                    $registro['valpago_atual'] = $notaAtual->VALPAGO;
                } else {
                    $registro['numeronota_real'] = null;
                    $registro['truncado'] = false;
                    $registro['ja_tinha_pagamento'] = false;
                    $registro['dtapago_atual'] = null;
                    $registro['valpago_atual'] = null;
                    //lista os numeronota candidatos quando ambiguo, so pra informar na tela
                    $registro['candidatos'] = array_map(function ($c) {
                        return $c->NUMERONOTA;
                    }, $candidatos);
                }

                $linhas[] = $registro;
            }
        }

        if (empty($linhas)) {
            return redirect()->back()->withErrors(['Nenhum registro de detalhe (tipo 1) foi encontrado nos arquivos enviados. Confira se são realmente arquivos de retorno CNAB 400.']);
        }

        return view('notas.pos-baixa', compact('linhas'));
    }

    //aplica de fato a baixa (DTAPAGO/VALPAGO) nas notas selecionadas na tela de
    //conferencia; so mexe nas linhas marcadas como pagamento (codigo 06/15/17) E
    //encontradas no banco E que o usuario deixou marcadas no checkbox
    public function confirmarBaixaRetorno(Request $request)
    {
        $selecionadas = json_decode($request->input('linhas_selecionadas', '[]'), true);

        if (empty($selecionadas)) {
            return redirect()->back()->withErrors(['Nenhuma linha selecionada para baixa.']);
        }

        $sucesso = [];
        $falha = [];
        $invalida = [];

        //os dados de "linhas_selecionadas" vem de um campo oculto preenchido no passo
        //anterior e ecoado de volta pelo navegador (o usuario pode alterar via devtools
        //antes de enviar); valida o formato de cada campo antes de usar em SQL
        foreach ($selecionadas as $linha) {
            $numeroNota = isset($linha['numeronota']) ? (string) $linha['numeronota'] : '';
            $dataPagamento = isset($linha['data_pagamento']) ? (string) $linha['data_pagamento'] : '';
            $valorPago = $linha['valor_pago'] ?? null;

            $numeroNotaValido = preg_match('/^[A-Za-z0-9\-]{1,30}$/', $numeroNota);
            $dataValida = preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dataPagamento);
            $valorValido = is_numeric($valorPago) && $valorPago >= 0 && $valorPago < 100000000;

            if (!$numeroNotaValido || !$dataValida || !$valorValido) {
                $invalida[] = $numeroNota !== '' ? $numeroNota : '(sem número)';
                continue;
            }

            $ok = $this->repository->darBaixaPagamento($numeroNota, $dataPagamento, (float) $valorPago);

            if ($ok) {
                $sucesso[] = $numeroNota;
            } else {
                $falha[] = $numeroNota;
            }
        }

        if (!empty($sucesso)) {
            array_push($this->msgInforma, count($sucesso) . ' nota(s) baixada(s) com sucesso: ' . implode(', ', $sucesso));
        }

        if (!empty($falha)) {
            array_push($this->msgInforma, count($falha) . ' nota(s) não encontrada(s) ao gravar (podem ter sido alteradas entre a conferência e a confirmação): ' . implode(', ', $falha));
        }

        if (!empty($invalida)) {
            array_push($this->msgInforma, count($invalida) . ' nota(s) ignorada(s) por dado inválido: ' . implode(', ', $invalida));
        }

        return redirect()->route('notas.index')->with('msgInforma', $this->msgInforma);
    }

    public function imprimirNfse($numnota, $codigo, Request $request)
    {

        //busca pela chave unica (numeronota), nao por numero_nfse: a numeracao da
        //Focus NFe reinicia por prestador e pode colidir com registros antigos da prefeitura
        $dadosNota = $this->repository->buscaNotaEmitidaPorNota($numnota);

        if (is_null($dadosNota)) {
            return redirect()->back()->withErrors(['Nota não encontrada no banco de dados.']);
        }

        $urlDanfse = $dadosNota->URL_DANFSE;

        if (empty($urlDanfse) && !empty($dadosNota->REF_FOCUS)) {
            //nota ainda estava em processamento na emissao; reconsulta a Focus NFe
            $resposta = $this->focusNfe->consultar($dadosNota->REF_FOCUS);
            $corpo = $resposta['body'];
            $urlDanfse = $corpo['url_danfse'] ?? '';

            if (!empty($urlDanfse)) {
                $this->repository->atualizaStatusFocus($numnota, $corpo['status'] ?? '', $urlDanfse, $corpo['caminho_xml_nota_fiscal'] ?? '');
            }
        }

        if (empty($urlDanfse)) {
            return redirect()->back()->withErrors(['NFSe ainda não disponível para impressão (em processamento na prefeitura).']);
        }

        $enviaremail = $request->has('email') ? $request->email : 'N';

        if ($enviaremail == 'S' && $this->trataItemVazio($dadosNota->EMAIL_CLIENTE) != '') {
            $pdfConteudo = file_get_contents($urlDanfse);
            Mail::to($this->trataItemVazio($dadosNota->EMAIL_CLIENTE))->send(new SendMailUser($pdfConteudo));
        }

        return redirect($urlDanfse);
    }

    //cancelamento via modal na tela de consulta de NFSe (POST, nao GET - cancelar e uma
    //acao que muda estado, nao deveria nunca ser um link simples). Aceita uma ou varias
    //notas de uma vez (numeronotas[]), com um motivo compartilhado - o botao de cancelar
    //de uma linha so e o caso de uma nota so nesse mesmo array.
    //
    //Redireciona de volta pra MESMA listagem (nao pra notas.index), via GET com
    //dtIni/dtFim na query string: a tela de onde o modal e aberto (posConsultarNfse) e
    //resultado de POST, entao um redirect()->back() cairia numa rota que so aceita POST
    //e quebraria ("GET method not supported"). Por isso posconsultarnfse agora aceita
    //GET tambem (ver rota) - e por ser GET, um F5 na resposta e seguro, sem reenviar o
    //cancelamento (mesmo motivo do ajuste em posEmitir()).
    public function posCancelarNota(Request $request)
    {
        $numeronotas = array_filter((array) $request->numeronotas, function ($n) {
            return trim($n) !== '';
        });

        $redirectParams = ['dtIni' => $request->dtIni, 'dtFim' => $request->dtFim];

        if (empty($numeronotas)) {
            return redirect()->route('notas.posconsultarnfse', $redirectParams)->withErrors(['Nenhuma nota selecionada para cancelamento.']);
        }

        if (empty(trim($request->motivo))) {
            return redirect()->route('notas.posconsultarnfse', $redirectParams)->withErrors(['Informe o motivo do cancelamento.']);
        }

        $sucesso = [];
        $falha = [];

        foreach ($numeronotas as $numeronota) {
            $dadosNota = $this->repository->buscaNotaEmitidaPorNota($numeronota);

            if (is_null($dadosNota) || empty($dadosNota->REF_FOCUS)) {
                $falha[] = $numeronota . ' (nota não encontrada ou sem referência da Focus NFe)';
                continue;
            }

            $resposta = $this->focusNfe->cancelar($dadosNota->REF_FOCUS, $request->motivo);

            if (!in_array($resposta['http_status'], [200, 202])) {
                $erro = $resposta['body']['mensagem'] ?? json_encode($resposta['body']);
                $falha[] = $numeronota . ' (' . $erro . ')';
                continue;
            }

            //mantem STATUS_FOCUS local coerente com o que a Focus NFe confirmou, sem
            //precisar de uma nova consulta pra refletir o cancelamento na tela
            $this->repository->atualizaStatusFocus($numeronota, 'cancelado', $dadosNota->URL_DANFSE, $dadosNota->CAMINHO_XML);
            $sucesso[] = $numeronota;
        }

        if (!empty($sucesso)) {
            array_push($this->msgInforma, count($sucesso) . ' NFSe(s) cancelada(s) com sucesso: ' . implode(', ', $sucesso));
        }

        if (!empty($falha)) {
            return redirect()->route('notas.posconsultarnfse', $redirectParams)
                ->with('msgInforma', $this->msgInforma)
                ->withErrors(['Falha ao cancelar ' . count($falha) . ' nota(s): ' . implode('; ', $falha)]);
        }

        return redirect()->route('notas.posconsultarnfse', $redirectParams)->with('msgInforma', $this->msgInforma);
    }




    public function preEditarNota($idcliente, $idnota)
    {
        $dados = $this->repository->buscaDetalheNota($idcliente, $idnota);

        return view('notas.pre-editarnota', compact('dados'));
    }


    public function posEditarNota(Request $request)
    {


        $dadosNota = array('idcliente' =>      $request->idcliente,  
                            'numeroNota' =>     $request->numeronota, 
                            'valorPago' =>      $this->formataValor($request->valpago),
                            'dataPago' =>       $request->dtapago
                        );

        $retornoSalvar = $this->repository->salvaDetalheNotaEmitida($dadosNota);


        if ($retornoSalvar[0] == false){

            array_push($erros, $retornoSalvar[1]);

            return redirect()->back()->withErrors($erros);
        }
        $this->msgInforma = ['Nota alterada com sucesso'];

        return $this->preConsultarNotasEmitidas();

    }


    public function encapsule(Request $request)
    {
        $url = $request->url; 
        return view('encapsule.index', compact('url'));
    }


    
}