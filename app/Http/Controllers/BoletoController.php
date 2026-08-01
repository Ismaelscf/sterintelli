<?php

namespace App\Http\Controllers;


use stdClass;
use App\Repositories\BoletoRepository;
use App\Repositories\NotaRepository;

use App\Model\Boleto\Remessa;
use App\Model\Report;
use App\Model\Config;

use Illuminate\Http\Request;

class BoletoController extends Controller
{

    protected $config;
    protected $cnpjBeneficiario;
    protected $carteira; 
    protected $agencia; 
    protected $conta;
    protected $dv_conta;
    protected $sequencial;
    protected $ambienteItau;
    //


    public function __construct()
    {

        //$this->middleware('auth');
      
        $this->repository = new BoletoRepository();
        $this->notaRepository = new NotaRepository();

        parent::__construct();

        //1-testes, 2-homologacao
        $this->ambienteItau = 1;
        //P-producao, H-homologacao
        $this->dadosBeneficiario = $this->repository->buscaDadosBeneficiario($this->ambienteItau);

        $this->config = [
            'tipo_ambiente' => $this->ambienteItau,
            'identificador' => $this->dadosBeneficiario->CNPJ,//CNPJ DA EMPRESA
            'itau_chave' 	=> $this->dadosBeneficiario->ITAU_CHAVE,
            'client_id'		=> $this->dadosBeneficiario->CLIENT_ID,
            'client_secret'	=> $this->dadosBeneficiario->CLIENT_SECRET
        ];

        $this->cnpjBeneficiario = $this->dadosBeneficiario->CNPJ; 
        $this->carteira = $this->dadosBeneficiario->CARTEIRA; 
        $this->agencia = $this->dadosBeneficiario->AGENCIA; 
        $this->conta = $this->zeroFill($this->dadosBeneficiario->CONTA,7); 
        $this->dv_conta = $this->dadosBeneficiario->DV_CONTA; 
        $this->sequencial = $this->dadosBeneficiario->SEQUENCIAL; 

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
   /* public function preEmitir($idcliente, Request $request)
    {
        $dtInicial = $request->query->has('dtini') ? 
                          $request->query->all()['dtini'] : null;
        $dtFinal = $request->query->has('dtfim') ? 
                          $request->query->all()['dtfim'] : null;


        $boletosPeriodo = $this->repository->buscaBoletoEmitido($idcliente, 
                        $dtInicial, $dtFinal);

        $msgAlerta = [];
        if ($this->repository->count > 0){
            
          array_push($msgAlerta, 'Existem boletos emitidos para esse período:' );

          foreach ($boletosPeriodo as $boleto) {

              $msg =  'NumBoleto: '. $boleto->NUMERO.
                  ' <br>  Data: '.$boleto->DATA.'  '.
                  ' Valor: '. $boleto->VALOR;

              array_push($msgAlerta, $msg);
          }
        }
          

        $dadosEmissor = $this->dadosEmissor;
        
        /*buscar os dados do período*
        $dadosEmissao = $this->repository->buscaDadosEmissao(
                                $idcliente, $dtInicial, $dtFinal
                              );

        
        $numRps = (int)$this->consultarUltimoSequencial() + 1;
        $horaNota = date("H:i:s");


        $estados = $this->repository->consultarEstadosCod();
        $municipios = $this->repository->consultarMunicipiosCod();

        return view('notas.pre-emitir', 
                    compact('dadosEmissor', 'dadosEmissao', 
                      'dtInicial', 'dtFinal', 'numRps',
                      'horaNota', 'idcliente',
                      'msgAlerta', 'estados', 'municipios'
                    ));

    }*/

    public function preConsultarBoletosEmitidos()
    {
        $clientes = $this->repository->consultarClientesCompleto();

        $msgInforma = $this->msgInforma;
        return view('boletos.pre-consboletosemitidos', compact('clientes', 'msgInforma'));
    }    

    public function posConsultarBoletosEmitidos(Request $request)
    {

        $dtIni = $request->dtIni;
        $dtFim = $request->dtFim;

        $boletos = $this->repository->consultarBoletosEmitidos($request->dtIni, 
                $request->dtFim, $request->cmbCliente);

        return view('boletos.pos-consboletosemitidos', compact('boletos','dtIni','dtFim'));
    }


    public function posEmitir($codcliente, $numnota, Request $request)
    {
        //try {       
                      
            $dadosNota = $this->notaRepository->buscaNotaEmitidaPorNota($numnota);
            if ($dadosNota == null){
                //return redirect()->back()->withErrors(['Nota não encontrada']);
                die('Nota não encontrada');
                
            }

            //verifica se já tem um boleto emitido
            $boletoEmitido = $this->repository->verificaBoletoEmitido($codcliente, $numnota);
            if($boletoEmitido){
                $arqNomeRet = 'boletos_emitidos/'.$numnota.'/'.$numnota.'_retorno.json';
                $str = file_get_contents($arqNomeRet);
                $json = json_decode (json_encode (json_decode($str)), FALSE);
                return $this->imprimirBoleto($json);                
            }

            $remessa = new Remessa($this->config);

            $nossoNumero = '';
            $dacNossoNumero = '';

            $nossoNumero = $this->zeroFill($this->sequencial, 8);

            //calculo do digito de certificação
            $dacNossoNumero = $this->modulo10($this->agencia . $this->conta . $this->carteira . $this->sequencial);
            $numCalcDac = $this->agencia . $this->conta . $this->carteira . $nossoNumero;
            $dacNossoNumero = $this->modulo10($numCalcDac);


            $boleto = [];

            //criando boleto para impressao
            $boleto = [
                'tipo_registro'								=> 1,
                'tipo_cobranca'								=> 1,
                'tipo_produto'								=> '00006',
                'subproduto'								=> '00008',
                'titulo_aceite'								=> 'N',
                'tipo_carteira_titulo'						=> $this->carteira,
                'nosso_numero'								=> $nossoNumero,
                'digito_verificador_nosso_numero'			=> $dacNossoNumero,
                'codigo_barras'								=> NULL, //'34191090080002322908140809520006387400000072258',
                'data_vencimento'							=> $this->formataDataEnvio($dadosNota->DTAVENCIMENTO) ,
                'valor_cobrado'								=> $this->formataValorBoleto($dadosNota->VALORNOTA),
                'seu_numero'								=> $this->zeroFill($numnota,15),
                'especie'									=> '01',
                'data_emissao'								=> date('Y-m-d'),
                //alterado para se a data do dia 'data_emissao'								=> $this->formataDataEnvio($dadosNota->DTANOTA),
                'data_limite_pagamento'						=> $this->formataDataEnvio($dadosNota->DTAVENCIMENTO),
                'tipo_pagamento'							=> 3,
                'indicador_pagamento_parcial'				=> false,
                //beneficiario
                'cpf_cnpj_beneficiario'						=> $this->zeroFill($this->cnpjBeneficiario,14),
                'agencia_beneficiario'						=> $this->agencia,
                'conta_beneficiario'						=> $this->conta, 
                'digito_verificador_conta_beneficiario'		=> $this->dv_conta,	
                //pagador
                'cpf_cnpj_pagador'							=> $dadosNota->CNPJ_CLIENTE,
                'nome_pagador'								=> substr($dadosNota->CLIENTE, 0, 30),//max 30
                'logradouro_pagador'						=> substr($dadosNota->ENDERECO_CLIENTE, 0, 40), //40
                'bairro_pagador'							=> substr($dadosNota->BAIRRO_CLIENTE, 0, 15), //15
                'cidade_pagador'							=> substr($dadosNota->MUNICIPIO_CLIENTE, 0, 20), //20
                'uf_pagador'								=> $dadosNota->UF_TOMADOR, //2
                'cep_pagador'								=> str_replace('-','', $dadosNota->CEP_CLIENTE),
                //moeda
                'codigo_moeda_cnab'							=> '09',
                //juros
                'tipo_juros'								=> 3,//Percentual mensal para incidência de juros após um dia corrido da Data de Vencimento
                'percentual_juros'							=> '000000200000',
                //multa
                'tipo_multa'								=> 2, //Quando se deseja cobrar um percentual do valor do título de multa após o vencimento. 
                'percentual_multa'							=> '000000200000',
                //grupo desconto
                'tipo_desconto'								=> '0',
                //recebimento divergente
                'tipo_autorizacao_recebimento'				=> '3', //Quando o título não deve aceitar pagamentos de valores divergentes ao da cobrança 3
                'tipo_valor_percentual_recebimento'			=> 'V',
                'valor_minimo_recebimento'					=> $this->formataValorBoleto($dadosNota->VALORNOTA),
                'percentual_minimo_recebimento'				=> '',
                'valor_maximo_recebimento'					=> $this->formataValorBoleto($dadosNota->VALORNOTA),
                'percentual_maximo_recebimento'				=> ''
            ];


            
            //adicioando boleto
            $remessa->addBoleto($boleto);

            $result = $remessa->enviar();

            $retorno = $result[0];
            $jsonRetorno = $result[1];

            //salva arquivo
            $arqNomeEnv = 'boletos_emitidos/'.$numnota.'/'.$numnota.'_envio.json';
            $arqNomeRet = 'boletos_emitidos/'.$numnota.'/'.$numnota.'_retorno.json';
            $dirname = dirname($arqNomeRet);
            if (!is_dir($dirname))
                mkdir($dirname, 0755, true);

            $fp = fopen($arqNomeEnv, 'w');
            fwrite($fp, json_encode($boleto));
            fclose($fp);


            $fp = fopen($arqNomeRet, 'w');
            fwrite($fp, json_encode($jsonRetorno));
            fclose($fp);


            $erros = [];

            if($retorno == false){

                if(isset($jsonRetorno->campos)){

                    foreach ($jsonRetorno->campos as $campo) {
                        $erro = $campo->campo.' - '.$campo->mensagem.' - '.$campo->valor;
                        array_push($erros, $erro);
                    }
                }

                if(isset($jsonRetorno->mensagem)){
                        array_push($erros, $jsonRetorno->mensagem);
                }                
                //return redirect()->back()->withErrors($erros);
                die(json_encode($erros));
            }

            //salvar boleto emitido
            $retornoSalvar = $this->repository->salvaBoletoEmitido($dadosNota, $jsonRetorno, $arqNomeRet);

            if ($retornoSalvar[0] == false){

                array_push($erros, 'Ocorreram erros.');
                array_push($erros, $retornoSalvar[1]);

                //return redirect()->back()->withErrors($erros);
                die(json_encode($erros));
            }

            //retorna a página inicial
            array_push($this->msgInforma, 'Boleto emitido com sucesso.');

            //imprimir boleto
            

        /*} catch (\Exception $e) {
            //return redirect()->back()->withErrors([$e->getMessage()]);
            return view('notas.pre-consultarnfse')->withErrors([$e->getMessage()]);
            die($e->getMessage());


        }*/
        //echo($this->msgInforma[0]);
        return $this->imprimirBoleto($jsonRetorno);


    }

    public function posImprimir($numnota)
    {
        $arqNomeRet = 'boletos_emitidos/'.$numnota.'/'.$numnota.'_retorno.json';
        $str = file_get_contents($arqNomeRet);
        $json = json_decode (json_encode (json_decode($str)), FALSE);
        return $this->imprimirBoleto($json);
    }    

    public function imprimirBoleto($jsonRetorno){

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
 
        //186
        $y = $pdf->GetY();
        $pdf->Box(165, '', 'Comprovante de Entrega', 0, '', 'R', 10, ['helvetica', 'B', 9]);
         
        $y1 = $pdf->GetY();
        $pdf->SetY($y1+10);
        $pdf->Image('images/logo.itau.jpg', 11.5, $y1+9, 28, 7);
        $pdf->Box(67, '', '', 0, 'B', 'C', 7);
        $pdf->Box(45, 'Vencimento', '   '.$this->formataDataRetonoBoleto($jsonRetorno->vencimento_titulo), 0, 'LTB', 'L', 7, $fontValor, 'B', false,$fontCaption);
        $pdf->Box(74, 'Valor do documento', $this->formataDinheiro($jsonRetorno->valor_titulo, true), 0, 'LTB', 'R', 7, $fontValor, 'B', false, $fontCaption);
 

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+7);
        $pdf->Box(93, 'Pagador', '   '.$jsonRetorno->pagador->nome_razao_social_pagador, 0, 'B', 'L', 7, $fontValor, 'B', false,$fontCaption);
        $pdf->Box(93, 'Beneficiário', '   '.$jsonRetorno->beneficiario->nome_razao_social_beneficiario, 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);        

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+7);
        $pdf->Box(186, 'Endereço Beneficiário / Sacador Avalista',  '   '.$jsonRetorno->beneficiario->logradouro_beneficiario. ' '.
                        $jsonRetorno->beneficiario->bairro_beneficiario, 0, 'B', 'L', 7, $fontValor, 'B', false,$fontCaption);           

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+7);
        $pdf->Box(60, 'Agência / Código Beneficiário', '   '.$jsonRetorno->beneficiario->agencia_beneficiario.'/'.
                    $jsonRetorno->beneficiario->conta_beneficiario.'-'.
                    $jsonRetorno->beneficiario->digito_verificador_conta_beneficiario, 0, 'B', 'L', 7, 
                    $fontValor, 'B', false,$fontCaption);
        $pdf->Box(46, 'Nosso Número','   '.'109/'.substr($jsonRetorno->nosso_numero,0,-1).'-'.substr($jsonRetorno->nosso_numero,-1), 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);        
        $pdf->Box(35, 'Nº Documento','   '.$jsonRetorno->seu_numero, 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);      
        $pdf->Box(45, 'CNPJ','   '.$this->formatar_cpf_cnpj($jsonRetorno->beneficiario->cpf_cnpj_beneficiario), 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);      

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+7);
        $pdf->Box(186, '', 'Para uso da Entregadora', 0, 'B', 'L', 15, ['helvetica', 'B', 9], 'B', false,$fontCaption);

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+15);
        $pdf->Box(32, 'Data', '', 0, 'LRB', 'L', 9, $fontValor, 'B', false,$fontCaption);
        $pdf->Box(65, 'Nome', '', 0, 'LB', 'L', 9, $fontValor, 'B', false, $fontCaption);        
        $pdf->Box(52, 'Assinatura recebedor', '', 0, 'LB', 'L', 9, $fontValor, 'B', false, $fontCaption);      
        $pdf->Box(37, 'Motivo da não entrega', '' , 0, 'LR', 'L', 9, $fontValor, 'B', false, $fontCaption);

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+9);
        $pdf->Box(186, '', '', 0, 'LR', 'L', 6, $fontValor, 'B', false,$fontCaption);

        //$pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 164)));
        $y1 = $pdf->GetY();
        $pdf->SetY($y1+5);
        $pdf->Box(5, '', '', 0, 'L', 'L', 5, $fontValor, 'B', false,$fontCaption);
        $pdf->SetLineStyle(array('dash' => '2'));
        $pdf->Box(10, '', '', 0, 'LT', 'L', 5, $fontValor, 'B', false,$fontCaption);
        $pdf->Box(65, '[  ] Mudou-se', '', 0, 'T', 'T', 5, $fontValor, 'B', false,$fontCaption);
        $pdf->Box(101, '[  ] Desconhecido', '', 0, 'TR', 'L', 5, $fontValor, 'B', false,$fontCaption);
        $pdf->SetLineStyle(array('dash' => 0));
        $pdf->Box(5, '', '', 0, 'R', 'L', 5, $fontValor, 'B', false,$fontCaption);

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+5);
        $pdf->Box(5, '', '', 0, 'L', 'L', 5, $fontValor, 'B', false,$fontCaption);
        $pdf->SetLineStyle(array('dash' => '2'));
        $pdf->Box(10, '', '', 0, 'L', 'L', 5, $fontValor, 'B', false,$fontCaption);
        $pdf->Box(65, '[  ] End. Insuficiente', '', 0, '', 'T', 7, $fontValor, 'B', false,$fontCaption);
        $pdf->Box(101, '[  ] Falecido', '', 0, 'R', 'L', 5, $fontValor, 'B', false,$fontCaption);
        $pdf->SetLineStyle(array('dash' => 0));
        $pdf->Box(5, '', '', 0, 'R', 'L', 5, $fontValor, 'B', false,$fontCaption);

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+5);
        $pdf->Box(5, '', '', 0, 'L', 'L', 5, $fontValor, 'B', false,$fontCaption);
        $pdf->SetLineStyle(array('dash' => '2'));
        $pdf->Box(10, '', '', 0, 'L', 'L', 5, $fontValor, 'B', false,$fontCaption);
        $pdf->Box(65, '[  ] Recusado', '', 0, '', 'L', 7, $fontValor, 'B', false,$fontCaption);
        $pdf->Box(101, '[  ] Outros _________', '', 0, 'R', 'L', 5, $fontValor, 'B', false,$fontCaption);
        $pdf->SetLineStyle(array('dash' => 0));
        $pdf->Box(5, '', '', 0, 'R', 'L', 5, $fontValor, 'B', false,$fontCaption);

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+5);
        $pdf->Box(5, '', '', 0, 'L', 'L', 5, $fontValor, 'B', false,$fontCaption);
        $pdf->SetLineStyle(array('dash' => '2'));
        $pdf->Box(10, '', '', 0, 'LB', 'L', 5, $fontValor, 'B', false,$fontCaption);
        $pdf->Box(65, '[  ] Ausente', '', 0, 'B', 'T', 5, $fontValor, 'B', false,$fontCaption);
        $pdf->Box(101, '', '', 0, 'BR', 'L', 5, $fontValor, 'B', false,$fontCaption);
        $pdf->SetLineStyle(array('dash' => 0));
        $pdf->Box(5, '', '', 0, 'R', 'L', 5, $fontValor, 'B', false,$fontCaption);

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+5);
        $pdf->Box(186, '', '', 0, 'LRB', 'L', 5, $fontValor, 'B', false,$fontCaption);

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+5);
        $pdf->SetLineStyle(array('dash' => '2'));
        $pdf->Box(186, '', '', 0, 'B', 'L', 40, $fontValor, 'B', false,$fontCaption);

        $pdf->SetLineStyle(array('dash' => 0));


        /** ---------------------------------------
         * fim da primeira parte
         * ----------------------------------------
         */

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+40);
        $pdf->Box(186, '', 'Autenticação mecânica', 0, '', 'C', 8, ['helvetica', '', 9], 'M', false, ['helvetica', '', 9], 'C');
        
        $y1 = $pdf->GetY();
        $pdf->SetY($y1+8);
        $pdf->Box(186, '', 'Recibo do Pagador', 0, '', 'R', 8, ['helvetica', 'B', 9], 'B', false, ['helvetica', '', 9], 'C');
         
        $y1 = $pdf->GetY();
        $pdf->SetY($y1+8);
        $pdf->Image('images/logo.itau.jpg', 11.5, $y1+7, 28, 7);
        $pdf->Box(60, '', '', 0, 'B', 'C', 7);
        $pdf->Box(46, 'Vencimento', '   '.$this->formataDataRetonoBoleto($jsonRetorno->vencimento_titulo), 0, 'LTB', 'L', 7, $fontValor, 'B', false,$fontCaption);
        $pdf->Box(80, 'Valor do documento', $this->formataDinheiro($jsonRetorno->valor_titulo, true), 0, 'LTB', 'R', 7, $fontValor, 'B', false, $fontCaption);        

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+7);
        $pdf->Box(93, 'Pagador', '   '.$jsonRetorno->pagador->nome_razao_social_pagador, 0, 'B', 'L', 7, $fontValor, 'B', false,$fontCaption);
        $pdf->Box(93, 'Beneficiário', '   '.$jsonRetorno->beneficiario->nome_razao_social_beneficiario, 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);        

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+7);
        $pdf->Box(186, 'Endereço Beneficiário / Sacador Avalista',  '   '.$jsonRetorno->beneficiario->logradouro_beneficiario. ' '.
                        $jsonRetorno->beneficiario->bairro_beneficiario, 0, 'B', 'L', 7, $fontValor, 'B', false,$fontCaption);           

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+7);
        $pdf->Box(60, 'Agência / Código Beneficiário', '   '.$jsonRetorno->beneficiario->agencia_beneficiario.'/'.
                    $jsonRetorno->beneficiario->conta_beneficiario.'-'.
                    $jsonRetorno->beneficiario->digito_verificador_conta_beneficiario, 0, 'B', 'L', 7, 
                    $fontValor, 'B', false,$fontCaption);
        $pdf->Box(46, 'Nosso Número','   '.'109/'.substr($jsonRetorno->nosso_numero,0,-1).'-'.substr($jsonRetorno->nosso_numero,-1), 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);        
        $pdf->Box(35, 'Nº Documento','   '.$jsonRetorno->seu_numero, 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);      
        $pdf->Box(45, 'CNPJ','   '.$this->formatar_cpf_cnpj($jsonRetorno->beneficiario->cpf_cnpj_beneficiario), 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);      



        $y1 = $pdf->GetY();
        $pdf->SetY($y1+6);
        $pdf->SetLineStyle(array('dash' => '2'));
        $pdf->Box(186, '', '', 0, 'B', 'L', 5, $fontValor, 'B', false,$fontCaption);

        $pdf->SetLineStyle(array('dash' => 0));

        /** ---------------------------------------
         * fim do topo do boleto
         * ----------------------------------------
         */        

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+7);
        $pdf->Image('images/logo.itau.jpg', 11.5, $y1+6, 28, 7);
        $pdf->Box(45, '', '', 0, 'B', 'C', 7);
        $pdf->Box(20, '341-7', '', 0, 'LB', 'C', 7, ['helvetica', 'B', 16], 'B', false, ['helvetica', 'B', 15], 'C');
        $pdf->Box(121, '', $this->formatar_linha_digitavel($jsonRetorno->numero_linha_digitavel), 0, 'LB', 'C', 7, ['helvetica', '', 11], 'B', false, $fontCaption);


        $y1 = $pdf->GetY();
        $pdf->SetY($y1+7);
        $pdf->Box(141, 'Local de pagamento', '   ATÉ O VENCIMENTO, PREFERENCIAMENTO NO ITAÚ                                                                                                     APÓS O VENCIMENTO, SOMENTE NO ITAÚ', 0, 'B', 
                                'L', 10, $fontValor, 'B', false,$fontCaption);
        $pdf->Box(45, 'Vencimento',$this->formataDataRetonoBoleto($jsonRetorno->vencimento_titulo), 0, 'LB', 
                                'R', 10, $fontValor, 'B', false, $fontCaption);        

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+10);
        $pdf->Box(141, 'Beneficiário',  '   '.$jsonRetorno->beneficiario->nome_razao_social_beneficiario, 0, 'B', 'L', 7, $fontValor, 'B', false,$fontCaption);      
        $pdf->Box(45, 'Agência / Código Beneficiário', '  '.$jsonRetorno->beneficiario->agencia_beneficiario.'/'.
                    $jsonRetorno->beneficiario->conta_beneficiario.'-'.
                    $jsonRetorno->beneficiario->digito_verificador_conta_beneficiario, 0, 
                    'LB', 'R', 7, $fontValor, 'B', false, $fontCaption);                              

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+7);
        $pdf->Box(35, 'Data do documento', '   '.$this->formataDataRetonoBoleto($jsonRetorno->data_emissao), 0, 'B', 'L', 7, 
                    $fontValor, 'B', false,$fontCaption);
        $pdf->Box(37, 'No Do documento', '   '.$jsonRetorno->seu_numero, 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);        
        $pdf->Box(21, 'Espécie doc.', '   '.$jsonRetorno->especie_documento, 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);      
        $pdf->Box(13, 'Aceite', '   N', 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption); 
        $pdf->Box(35, 'Data Processamento', '   '.$this->formataDataRetonoBoleto($jsonRetorno->data_processamento), 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption); 
        $pdf->Box(45, 'Nosso número',  '109/'.substr($jsonRetorno->nosso_numero,0,-1).'-'.substr($jsonRetorno->nosso_numero,-1), 0, 'LB', 'R', 7, $fontValor, 'B', false, $fontCaption);     


        $y1 = $pdf->GetY();
        $pdf->SetY($y1+7);
        $pdf->Box(35, 'Uso do Banco','', 0, 'B', 'L', 7, $fontValor, 'B', false,$fontCaption);
        $pdf->Box(24, 'Carteira', '   109', 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);        
        $pdf->Box(13, 'Espécie', '   R$', 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption);      
        $pdf->Box(34, 'Quantidade', '', 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption); 
        $pdf->Box(35, 'Valor', '', 0, 'LB', 'L', 7, $fontValor, 'B', false, $fontCaption); 
        $pdf->Box(45, '(=) Valor do Documento', $this->formataDinheiro($jsonRetorno->valor_titulo, true), 0, 'LB', 'R', 7, $fontValor, 'B', false, $fontCaption);  

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+7);
        $pdf->Box(141, 'Instruções de responsabiliadde do BENEFICIÁRIO. Qualquer dúvida sobre este boleto, contate o BENEFICIÁRIO.',  '', 0, 
                            '', 'L', 6, $fontValor, 'B', false, $fontCaption);       
        $pdf->Box(45, '(-) Desconto/Abatimento', '', 0, 'LB', 'R', 6, $fontValor, 'B', false, $fontCaption);                              

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+6);
        $pdf->Box(141, 'DEVOLVER APÓS 90 DIAS DO VENCIMENTO APÓS O VENCIMENTO COBRAR XXX POR DIA DE ATRASO',  '', 0, 
                            '', 'L', 6, $fontValor, 'B', false, $fontCaption);       
        $pdf->Box(45, '', '', 0, 'LB', 'R', 6, $fontValor, 'B', false, ['helvetica', 'B', 5]);                              

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+6);
        $pdf->Box(141, 'CASO NÃO CONSIGA PAGAR ATÉ O VENCIMENTO ENTRAR EM CONTATO COM A EMPRESA. ',  '', 0, 
                            '', 'L', 6, $fontValor, 'B', false, $fontCaption);       
        $pdf->Box(45, '(+) Mora/Multa', '', 0, 'LB', 'R', 6, $fontValor, 'B', false, $fontCaption);        

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+6);
        $pdf->Box(141, 'O BOLETO ESTÁ NEGATIVANDO APÓS 5 DIAS.',  '', 0, 
                            '', 'L', 6, $fontValor, 'B', false, $fontCaption);       
        $pdf->Box(45, '', '', 0, 'LB', 'R', 6, $fontValor, 'B', false, $fontCaption);        

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+6);
        $pdf->Box(141, '',  '', 0, 
                            'B', 'L', 6, $fontValor, 'B', false, $fontCaption);       
        $pdf->Box(45, '(=) Valor Cobrado', '', 0, 'LB', 'R', 6, $fontValor, 'B', false, $fontCaption);     

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+7);
        $pdf->Box(25, 'Pagador: ', '', 0, '', 'L', 4, $fontValor, 'B', false,$fontCaption);
        $pdf->Box(101, $jsonRetorno->pagador->nome_razao_social_pagador, '' , 0, '', 'L', 4, $fontValor, 'B', false, ['helvetica', 'B', 7]);    
        $pdf->Box(60, 'CNPJ: '.$this->formatar_cpf_cnpj($jsonRetorno->pagador->cpf_cnpj_pagador), '' , 0, '', 'L', 4, $fontValor, 'B', false, ['helvetica', 'B', 7]);    

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+5);
        $pdf->Box(25, 'Endereço: ', '', 0, '', 'L', 4, $fontValor, 'B', false,$fontCaption);
        $pdf->Box(161, $jsonRetorno->pagador->logradouro_pagador.''.$jsonRetorno->pagador->bairro_pagador
                        .''.$jsonRetorno->pagador->cidade_pagador.'-'.$jsonRetorno->pagador->uf_pagador, '' , 0, 
                        '', 'L', 4, $fontValor, 'B', false, ['helvetica', 'B', 7]);    

        $y1 = $pdf->GetY();
        $pdf->SetY($y1+5);
        $pdf->Box(25, 'Sacador/Avalista: ', '', 0, 'B', 'L', 4, $fontValor, 'B', false,$fontCaption);
        $pdf->Box(161, '', '', 0, 'B', 'L', 4, $fontValor, 'B', false, $fontCaption);    


        $params = $pdf->serializeTCPDFtagParameters(
            array($jsonRetorno->numero_linha_digitavel, 'I25', '', '', 120, 15, 1, 
            array('position'=>'S',  'bgcolor'=>array(255,255,255)), 'N'));
        

        
        $y1 = $pdf->GetY();
        $pdf->SetY($y1+6);
        //$html = '<tcpdf method="write1DBarcode" params="'.$params.'" />'; 
        //$pdf->writeHTML($html, true, false, true, false, '');

        $style = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            //'border' => true,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255),
            //'text' => true,
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 4
        );

        $pdf->write1DBarcode($jsonRetorno->numero_linha_digitavel, 'I25', '', '', '', 18, 0.4, $style, 'N');

        $pdf->SetY($y1+5);
        $pdf->Box(125, '', '', 0, '', 'C', 5);
        $pdf->Box(61, '', 'Ficha de Compensação', 0, '', 'R', 5,  ['helvetica', 'B', 9], 'B', false, ['helvetica', 'B', 9]);
        
        $y1 = $pdf->GetY();
        $pdf->SetY($y1+5);
        $pdf->Box(125, '', '', 0, '', 'C', 5);
        $pdf->Box(61, '', 'Autenticação Mecânica', 0, '', 'R', 5,  ['helvetica', '', 7], 'B', false, ['helvetica', 'B', 11]);
        
        
        echo $pdf->Output('boleto.pdf', 'I');
    }

    function codigoBarra($linhaDigitavel) {
        $codigo = $linhaDigitavel;
        $barcodes = array('00110', '10001', '01001', '11000', '00101', '10100', '01100', '00011', '10010', '01010');
        $barraStart = '<div class="barcode"><div class="black thin"></div><div class="white thin"></div><div class="black thin"></div><div class="white thin"></div>';
        $barraStop = '<div class="black large"></div><div class="white thin"></div><div class="black thin"></div></div>';
        $retorno = "";

        for ($a = 9; $a >= 0; $a--) {
            for ($b = 9; $b >= 0; $b--) {
                $ind = ($a * 10) + $b;
                $texto = "";

                for ($c = 1; $c < 6; $c++) {
                    $texto .= substr($barcodes[$a], ($c - 1), 1) . substr($barcodes[$b], ($c - 1), 1);
                }
                $barcodes[$ind] = $texto;
            }
        }

        while (strlen($codigo) > 0) {
            $codEsq = (int) round($this->esquerda($codigo, 2));
            $codigo = $this->direita($codigo, strlen($codigo) - 2);
            $binario = $barcodes[$codEsq];

            for ($i = 1; $i < 11; $i += 2) {
                $retorno .= "<div class='black " . (substr($binario, ($i - 1), 1) == "0" ? "thin" : "large") . "'></div>";
                $retorno .= "<div class='white " . (substr($binario, $i, 1) == "0" ? "thin" : "large") . "'></div>";
            }
        }

        $style = "<style>.barcode .thin.black {
            border-left-width: 1px;
        }
        .barcode .thin.white {
            width: 1px;
        }
        .barcode .large.black {
            border-left-width: 3px;
        }
        .barcode .large.white {
            width: 3px;
        }
        .barcode .black {
            border-color: #000;
            border-left-style: solid;
            width: 0;
        }
        .barcode .white {
            background: none repeat scroll 0 0 #fff;
        }
        
        .barcode div {
            display: inline-block;
            height: 100%;
        }
        </style>";

        return $style.$barraStart . $retorno . $barraStop;
    }

    protected static function formataDinheiro($valor, $mostrar_zero = false)
    {
        return $valor ? number_format($valor, 2, ',', '.') : ($mostrar_zero ? '0,00' : '');
    }

    function esquerda($entra,$comp){
        return substr($entra,0,$comp);
    }
     
    function direita($entra,$comp){
        return substr($entra,strlen($entra)-$comp,$comp);
    }
    
}
