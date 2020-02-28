<?php

namespace App\Http\Controllers;

use App\Repositories\FaturamentoRepository;

use Illuminate\Http\Request;


use App\Report;
use App\Config;
use stdClass;


class FaturamentoController extends Controller
{
    public function __construct(stdClass $rps)
    {
      $this->repository = new FaturamentoRepository();
    }


    public function index()
    {
        //$notas = $this->repository->buscaNotas();   
        return view('faturamento.index');//, compact('notas'));

    }

    public function preConsultarFaturamento($tipo)
    {

    	if ($tipo == 'P')
    		$tipoDesc = 'Período';
    	else
    		$tipoDesc = 'Cliente';

    	$clientes = $this->repository->consultarClientesCompleto();
    	$estados = $this->repository->consultarEstados();
    	$municipios = $this->repository->consultarMunicipios();


        return view('faturamento.pre-consultarfat
            ', compact('tipo', 'tipoDesc', 'clientes', 'estados', 'municipios'));
    }


    public function posConsultarFaturamento($tipo, Request $request)
    {

        $dtIni = $request->dtIni;
        $dtFim = $request->dtFim;

        //periodo
        if($tipo == 'P')
            $lista = $this->repository->consultarFatPeriodo($request->dtIni, 
                $request->dtFim, $request->cmbCliente, $request->cmbEstado, $request->cmbMunicipio);
        else
            //cliente
            $lista = '';
        
        return view('faturamento.pos-consultarfat', compact('lista','dtIni', 'dtFim'));
    }

    public function detalharFaturamento($idCliente, Request $request)
    {

        $dtIni = $request->dtini;
        $dtFim = $request->dtfim;
        $cliente = $request->cliente;

        $lista = $this->repository->consultarDetFatPeriodo($dtIni, 
                $dtFim, $idCliente);
        
        return view('faturamento.detalhefat', compact('lista', 'dtIni', 'dtFim', 'cliente'));
    }    

    public function imprimirFaturamento($idCliente, Request $request){
        
        $pdf = new Report(new Config('Faturamento'));
        $pdf->SetMargins(10.5, 5, 10.5);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(False, 0);
        $pdf->SetCellPaddings(0, 0, 0, 0);
        $pdf->SetLineStyle(array('width' => 0.3));
        $pdf->AddPage();

        // set font
        $pdf->SetFont('helvetica', '', 8);


        $dadosFaturamento = $this->repository->consultaFaturamento( 
                                    $request->dtini, 
                                    $request->dtfim,
                                    $idCliente);

        $periodo = "Período: ".$request->dtini." - ".$request->dtfim;

        $header = '
            <table class="tabela"  cellpadding="10" style="vertical-align: top; 
               font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px;">
               <tr>
                <td style="padding: 12px; display: inline-block; width:60%;">
                  <p style="margin-bottom: 0.75rem;"><b>'.$dadosFaturamento->FANTASIA.'</b></p>
                      <p style="margin-top: -0.375rem; margin-bottom: 0;">Razão Social: '.$dadosFaturamento->NOME.'</p>
                      <p style="margin-bottom: 10px;">'.$periodo.'</p>
                                </td>
                <td>
                <img src="/images/logo.small.png"></td>
               </tr>
            </table>

            <h2 style="text-align:center">FATURAMENTO CONSOLIDADO</h2>
              <table border="1" cellspacing="0" cellpadding="4" >
             
                        <tr >
                          <td width="400">Material</td>
                          <td width="60"  align="center">Unitário</td>
                          <td width="60"  align="center">Qtd</td>
                          <td width="120">Valor</td>
                        </tr>
                  ';

                $dadosItens = $this->repository->consultaFaturamentoItens(
                                    $request->dtini, 
                                    $request->dtfim,
                                    $idCliente);
                $i = 0;
                $pg = 1;
                $html = $header;
                foreach ($dadosItens as $item) {
                    $html .= '<tr>
                              <td width="400">&nbsp;'.$item->NOME.'</td>
                              <td width="60" align="center">'.$item->VAL_UNITARIO.'</td>
                              <td width="60" align="center">'.$item->QTD.'</td>
                              <td width="120" align="right">'.$item->TOTAL.'</td>
                              </tr>';  

                    $i++;
                    if ($i> 35){
                          
                          $html .= '</table><p>Página '.$pg.'</p>';
                          $pdf->writeHTML($html, true, false, true, false, '');
                          $pdf->AddPage();
                          $html = $header;
                          $i = 0;
                          $pg++;

                    }
                }   

              $html .= '
                 
              </table>

              <table border="0" cellspacing="0" cellpadding="4">
                      <tr>
                             
                          <td width="400"></td>
                          <td width="120" align="right">Total:</td>
                          <td width="120" align="right">'.$dadosFaturamento->TOTAL.'</td>
                      </tr> 
                      <tr>
                          <td width="400px">&nbsp;Recebido por: ____________________________________</td>
                          <td width="120" align="right">Desconto:</td>
                          <td width="120" align="right">'.$dadosFaturamento->DESCONTO.'</td>
                      </tr>
                      <tr>
                          <td width="400px"></td>
                          <td width="120" align="right">Transporte:</td>
                          <td width="120" align="right">'.$dadosFaturamento->TRANSPORTE.'</td>
                      </tr> 
                      <tr>
                          <td width="400px">&nbsp;Em: _____ / _____ / _______</td>
                          <td width="120" align="right">Total a Pagar:</td>
                          <td width="120" align="right">'.$dadosFaturamento->TOTALD.'</td>
                      </tr>
              </table> <p>Página '.$pg.'</p>';

        // output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');




        // $txtPdf = $pdf->Output('nfse.pdf', 'S');
        // file_put_contents(UPLOAD_PATH . $target, $txtPdf);
      
        echo $pdf->Output('nfse.pdf', 'I');        
    }
}
