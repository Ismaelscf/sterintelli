@extends('layout.app') @section('content')
 <form action="{{ url('notas/enviar') }}" method="POST">
   @csrf 

 <input type="hidden"   id="tiporps" name="tiporps"  value= "RPS">
 <input type="hidden"   id="situacaorps" name="situacaorps"  value= "N">
 <input type="hidden"   name="ambiente" id="ambiente" value='H'>
 <input type="hidden"   id="dtInicial" name="dtInicial"  value= "{{$dtInicial}}">
 <input type="hidden"   id="dtFinal" name="dtFinal"  value= "{{$dtFinal}}}">
 
 <input type="hidden"   id="qtdade" name="qtdade"  value= "1">
<div class="container">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="page-header">
                    <h1>
					Emissão de nota fiscal de serviço
					</h1>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Dados do prestador</div>

                <div class="card-body">

                    <div class="row">
                        <div class="col-md-3">
                            <div class="fb-text form-group field-cnpj">
                                <label for="cnpj" class="fb-text-label">CNPJ<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="cnpjprestador" id="cnpjprestador" required="required" aria-required="true" value="{{ $dadosEmissor['CNPJ'] }}">
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="fb-text form-group field-razao">
                                <label for="razao" class="fb-text-label">Razão Social<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="razaosocialprestador" id="razaosocialprestador" required="required" aria-required="true" value="{{ $dadosEmissor['RAZAOSOCIAL'] }}">
                            </div>
                        </div>

                    </div>


                    <div class="row">
                        <div class="col-md-3">

                            <div class="fb-text form-group field-im">
                                <label for="im" class="fb-text-label">Inscrição municpal<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="inscricaomunicipalprestador" id="iinscricaomunicipalprestadorm" required="required" aria-required="true" value="{{ $dadosEmissor['IM'] }}">
                            </div>                            
                        </div>

                        <div class="col-md-9">
                            <div class="fb-text form-group field-token">
                                <label for="token" class="fb-text-label">Token de Envio<span class="fb-required">*</span></label>
                                <input type="password" class="form-control" name="token" id="token" required="required" aria-required="true" value="{{ $dadosEmissor['TOKEN'] }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            
                            
                            <div class="fb-text form-group field-razao">
                                <label for="serierps" class="fb-text-label">Série RPS<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="serierps" id="serierps" required="required" aria-required="true" value="NF" readonly>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="fb-text form-group field-token">
                                <label for="numerorps" class="fb-text-label">No RPS<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="numerorps" id="numerorps" required="required" aria-required="true" value="{{$numRps}}" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-text form-group field-token">
                                <label for="dataemissaorps" class="fb-text-label">Data nota<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="dataemissaorps" id="dataemissaorps" required="required" aria-required="true" value="{{$dataNota}}" readonly style="background: white">
                                <script>
                                $('#dataNota').datepicker({locale:'pt-br', format:'dd/mm/yyyy'});
                              </script>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-text form-group field-token">
                                <label for="horaemissaorps" class="fb-text-label">Hora nota<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="horaemissaorps" id="horaemissaorps" required="required" aria-required="true" value="{{$horaNota}}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="fb-text form-group field-token">
                                <label for="serieprestacao" class="fb-text-label">Série prestação<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="serieprestacao" id="serieprestacao" required="required" aria-required="true" value="99" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">

                        </div>
                        <div class="col-md-2">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Dados do tomador</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                                <div class="fb-text form-group field-cnpjtomador">
                                    <label for="cpfcnpjtomador" class="fb-text-label">CPF/CNPJ do Tomador<span class="fb-required">*</span></label>
                                    <input type="text" class="form-control" name="cpfcnpjtomador" id="cpfcnpjtomador" required="required" aria-required="true" value="{{$dadosEmissao['CNPJ_TOMADOR']}}">
                                </div>
                        </div>
                        <div class="col-md-3">
                                <div class="fb-text form-group field-inscricaomunicipaltomador">
                                    <label for="inscricaomunicipaltomador" class="fb-text-label">Inscrição municipal do tomador<span class="fb-required">*</span></label>
                                    <input type="text" class="form-control" name="inscricaomunicipaltomador" id="inscricaomunicipaltomador" required="required" aria-required="true" value="{{$dadosEmissao['IM_TOMADOR']}}">
                                </div>
                        </div>
                        <div class="col-md-6">
                                 <div class="fb-text form-group field-razaotomador">
                                <label for="razaotomador" class="fb-text-label">Razão social do Tomador<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="razaosocialtomador" id="razaosocialtomador" required="required" aria-required="true" value="{{$dadosEmissao['FANTASIA_TOMADOR']}}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="fb-select form-group field-tipologradourotomador">
                                    <label for="tipologradourotomador" class="fb-select-label">Tipo de logradouro do tomador</label>
                                    <select class="form-control" name="tipologradourotomador" id="tipologradourotomador">
                                        <option value="Avenida">Avenida</option>      
                                        <option value="Rua" selected="true">Rua</option>
                                        <option value="Rodovia">Rodovia</option>
                                        <option value="Ruela">Ruela</option>
                                        <option value="Rio">Rio</option>
                                        <option value="Sítio">Sítio</option>
                                        <option value="Sup_Quadra">Sup Quadra</option>
                                        <option value="Travessa">Travessa</option>
                                        <option value="Vale">Vale</option>
                                        <option value="Via">Via</option>
                                        <option value="Viaduto">Viaduto</option>
                                        <option value="Viela">Viela</option>
                                        <option value="Vila">Vila</option>
                                        <option value="Vargem">Vargem</option>                                      
                                    </select>
                                </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fb-text form-group field-logradourotomador">
                                <label for="logradourotomador" class="fb-text-label">Logradouro do tomador</label>
                                <input type="text" class="form-control" name="logradourotomador" id="logradourotomador" value="{{$dadosEmissao['END_TOMADOR']}}">
                            </div>
                        </div>
                        <div class="col-md-2">
                                <div class="fb-text form-group field-numeroenderecotomador">
                                    <label for="numeroenderecotomador" class="fb-text-label">Número</label>
                                    <input type="text" class="form-control" name="numeroenderecotomador" id="numeroenderecotomador" value="{{$dadosEmissao['NUM_TOMADOR']}}">
                                </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="fb-textarea form-group field-complementoenderecotomador">
                                <label for="complementoenderecotomador" class="fb-textarea-label">Complemento</label>
                                <input type="text" class="form-control" class="form-control" name="complementoenderecotomador" id="complementoenderecotomador">
                            </div>
                        </div>

                        <div class="col-md-4">
                                <div class="fb-select form-group field-tipobairrotomador">
                                    <label for="tipobairrotomador" class="fb-select-label">Tipo bairro tomador</label>
                                    <select class="form-control" name="tipobairrotomador" id="tipobairrotomador">
                                        <option value="Bairro" selected="true"> Bairro</option>
                                        <option value="Bosque" >Bosque</option>
                                        <option value="Chácara" >Chácara</option>
                                        <option value="Conjunto" >Conjunto</option>
                                        <option value="Desmembramento" >Desmembram</option>
                                        <option value="Distrito" >Distrito</option>
                                        <option value="Favela" >Favela</option>
                                        <option value="Fazenda" >Fazenda</option>
                                        <option value="Gleba" >Gleba</option>
                                        <option value="Horto" >Horto</option>
                                        <option value="Jardim" >Jardim</option>
                                        <option value="Loteamento" >Loteamento</option>
                                        <option value="Núcleo" >Núcleo</option>
                                        <option value="Parque" >Parque</option>
                                        <option value="Residencial" >Residencia</option>
                                        <option value="Sítio" >Sítio</option>
                                        <option value="Tropical" >Tropical</option>
                                        <option value="Vila" >Vila</option>
                                        <option value="Zona" >Zona</option>
                                    </select>
                                </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fb-text form-group field-bairrotomador">
                                <label for="bairrotomador" class="fb-text-label">Bairro</label>
                                <input type="text" class="form-control" name="bairrotomador" id="bairrotomador" value="{{$dadosEmissao['BAIRRO_TOMADOR']}}">
                            </div>                            
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="fb-text form-group field-ceptomador">
                                <label for="ceptomador" class="fb-text-label">CEP<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="ceptomador" id="ceptomador" required="required" aria-required="true" value="{{$dadosEmissao['CEP_TOMADOR']}}">
                            </div>
                        </div>

                        <div class="col-md-4">
                                <div class="fb-select form-group field-cidadetomador">
                                    <label for="cidadetomador" class="fb-select-label">Cidade do tomador</label>
                                    <select class="form-control" name="cidadetomador" id="cidadetomador">
                                       <option value="0000921" selected="true">São Luis</option>
                                       <option value="00090631" selected="true">Teresina</option>
                                    </select>
                                </div>
                        </div>
                        <div class="col-md-4">
                                <div class="fb-text form-group field-emailtomador">
                                    <label for="emailtomador" class="fb-text-label">E-mail tomador<span class="fb-required">*</span></label>
                                    <input type="text" class="form-control" name="emailtomador" id="emailtomador" required="required" aria-required="true" value="{{$dadosEmissao['EMAIL_TOMADOR']}}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            
                            
                    </div>
                </div>
            </div>
        </div>
    </div>

    <br>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Dados da nota</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="fb-select form-group field-codigoatividade">
                                        <label for="codigoatividade" class="fb-select-label">Código de Atividade<span class="fb-required">*</span></label>
                                        <select class="form-control" name="codigoatividade" id="codigoatividade" required="required" aria-required="true">
                                            <option value="812900000" selected="true">812900000 - ATIVIDADES DE LIMPEZA NAO ESPECIFICADAS ANTERIORMENTE</option>
                                        </select>
                                    </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="fb-select form-group field-codigoservico">
                                        <label for="codigoservico" class="fb-select-label">Código do Serviço<span class="fb-required">*</span></label>
                                        <select class="form-control" name="codigoservico" id="codigoservico" required="required" aria-required="true">
                                            <option value="0709" selected="true">0709 - VARRICAO, COLETA, REMOCAO, INCINERACAO, TRATAMENTO, RECICLAGEM, SEPARACAO E DESTINACAO FINAL DE LIXO, REJEITOS E OUTROS RESIDUOS QUAISQUER</option>
                                        </select>
                                    </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="fb-select form-group field-municipioprestacao">
                                <label for="municipioprestacao" class="fb-select-label">Municipio de prestacao<span class="fb-required">*</span></label>
                                <select class="form-control" name="municipioprestacao" id="municipioprestacao" required="required" aria-required="true">
                                    <option value="0000921" selected="true">São Luis</option>
                                    <option value="00090631" selected="true">Teresina</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-select form-group field-tiporecolhimento">
                                        <label for="tiporecolhimento" class="fb-select-label">Tipo de recolhimento<span class="fb-required">*</span></label>
                                        <select class="form-control" name="tiporecolhimento" id="tiporecolhimento" required="required" aria-required="true">
                                            <option value="A" selected="true">A Receber</option>
                                            <option value="R">Retido na Fonte</option>
                                        </select>
                                    </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-select form-group field-tributacao">
                                <label for="tributacao" class="fb-select-label">Tributação<span class="fb-required">*</span></label>
                                <select class="form-control" name="tributacao" id="tributacao" required="required" aria-required="true">
                                    <option value="C">"Isenta de ISS</option>
                                    <option value="E">Não Incidência no Município</option>
                                    <option value="F">Imune</option>
                                    <option value="K">Exigibilidd Susp.Dec.J/Proc.A</option>
                                    <option value="N">Não Tributável</option>
                                    <option value="T" selected="true">Tributável</option>
                                    <option value="G">Tributável Fixo</option>
                                    <option value="H">Tributável S.N.</option>
                                    <option value="M">Micro Empreendedor Individual (MEI)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-select form-group field-operacao">
                                        <label for="operacao" class="fb-select-label">Operação<span class="fb-required">*</span></label>
                                        <select class="form-control" name="operacao" id="operacao" required="required" aria-required="true">
                                            <option value="A" selected="true">Sem Dedução</option>
                                            <option value="B">Com Dedução/Materiais</option>
                                            <option value="C">Imune/Isenta de ISSQN</option>
                                            <option value="D">Devolução/Simples Remessa</option>
                                            <option value="J">Intemediação</option>

                                        </select>
                                    </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="fb-text form-group field-aliquotapis">
                                <label for="aliquotapis" class="fb-text-label">Aliquota PIS<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="aliquotapis" value="{{$dadosEmissao['ALQ_PIS']}}" id="aliquotapis" required="required" aria-required="true">
                            </div>
                        </div>
                        <div class="col-md-3">

                                    <div class="fb-text form-group field-valorpis">
                                        <label for="valorpis" class="fb-text-label">Valor PIS<span class="fb-required">*</span></label>
                                        <input type="text" class="form-control" name="valorpis" value="{{$dadosEmissao['VAL_PIS']}}" id="valorpis" required="required" aria-required="true">
                                    </div>
                        </div>
                        <div class="col-md-3">
                                                                <div class="fb-text form-group field-aliquotacofins">
                                        <label for="aliquotacofins" class="fb-text-label">Aliquota COFINS<span class="fb-required">*</span></label>
                                        <input type="text" class="form-control" name="aliquotacofins" value="{{$dadosEmissao['ALQ_CONFINS']}}" id="aliquotacofins" required="required" aria-required="true">
                                    </div>
                        </div>
                        <div class="col-md-3">
                                                        <div class="fb-text form-group field-valorcofins">
                                <label for="valorcofins" class="fb-text-label">Valor COFINS<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="valorcofins" value="{{$dadosEmissao['VAL_CONFINS']}}" id="valorcofins" required="required" aria-required="true">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">

                            <div class="fb-text form-group field-aliquotainss">
                                <label for="aliquotainss" class="fb-text-label">Aliquota INSS<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="aliquotainss" value="{{$dadosEmissao['ALQ_INSS']}}" id="aliquotainss" required="required" aria-required="true">
                            </div>
                        </div>
                        <div class="col-md-3">
                                                                <div class="fb-text form-group field-valorinss">
                                        <label for="valorinss" class="fb-text-label">Valor INSS<span class="fb-required">*</span></label>
                                        <input type="text" class="form-control" name="valorinss" value="{{$dadosEmissao['VAL_INSS']}}" id="valorinss" required="required" aria-required="true">
                                    </div>
                        </div>
                        <div class="col-md-3">
                                                                <div class="fb-text form-group field-aliquotair">
                                        <label for="aliquotair" class="fb-text-label">Aliquota IR<span class="fb-required">*</span></label>
                                        <input type="text" class="form-control" name="aliquotair" value="{{$dadosEmissao['ALQ_IR']}}" id="aliquotair" required="required" aria-required="true">
                                    </div>
                        </div>
                        <div class="col-md-3">
                                                        <div class="fb-text form-group field-valorir">
                                <label for="valorir" class="fb-text-label">Valor IR<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="valorir" value="{{$dadosEmissao['VAL_IR']}}" id="valorir" required="required" aria-required="true">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">

                            <div class="fb-text form-group field-aliquotacsll">
                                <label for="aliquotacsll" class="fb-text-label">Aliquota CSSL</label>
                                <input type="text" class="form-control" name="aliquotacsll" value="{{$dadosEmissao['ALQ_CSLL']}}" id="aliquotacsll">
                            </div>
                        </div>
                        <div class="col-md-3">


                                    <div class="fb-text form-group field-valorcsll">
                                        <label for="valorcsll" class="fb-text-label">Valor CSLL<span class="fb-required">*</span></label>
                                        <input type="text" class="form-control" name="valorcsll" value="{{$dadosEmissao['VAL_CSLL']}}" id="valorcsll" required="required" aria-required="true">
                                    </div>
                        </div>
                        <div class="col-md-3">
                        </div>
                        <div class="col-md-3">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="fb-text form-group field-aliquotaatividade">
                                <label for="aliquotaatividade" class="fb-text-label">Aliquota da atividade<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="aliquotaatividade" id="aliquotaatividade" required="required" aria-required="true" value="{{$dadosEmissao['ALQ_ISS']}}">
                            </div>
                        </div>
                        <div class="col-md-3">
                             <div class="fb-text form-group field-token">
                                <label for="vTotServ" class="fb-text-label">Total Nota<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="vTotServ" id="vTotServ" required="required" aria-required="true" value="{{$dadosEmissao['TOTAL_C_DESC']}}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-text form-group field-token">
                                <label for="vTotDeduc" class="fb-text-label">Total Deduções<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="vTotDeduc" id="vTotDeduc" required="required" aria-required="true" value="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-text form-group field-token">
                                <label for="valoriss" class="fb-text-label">Valor ISS<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="valoriss" id="valoriss" required="required" aria-required="true" value="{{$dadosEmissao['VAL_ISS']}}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">

                            <div class="fb-textarea form-group field-descricaorps">
                                <label for="descricaorps" class="fb-textarea-label">Descrição da Nota<span class="fb-required">*</span></label>
                                <textarea type="textarea" class="form-control" name="descricaorps" rows="3" id="descricaorps" required="required" aria-required="true">{{$dadosEmissao['MSGNF']}}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <br>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Itens da nota</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="rendered-form">
                                <div class="fb-textarea form-group field-discriminacaoservico">
                                    <label for="discriminacaoservico" class="fb-textarea-label">Discriminação do serviço<span class="fb-required">*</span></label>
                                    <textarea type="textarea" class="form-control" name="discriminacaoservico" id="discriminacaoservico" required="required" aria-required="true">{{$dadosEmissor['DESC_SERVICO']}}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                                                <div class="col-md-3">
                            <div class="fb-select form-group field-tributavel">
                                <label for="tributavel" class="fb-select-label">Tributável</label>
                                <select class="form-control" name="tributavel" id="tributavel">
                                    <option value="S" selected="true">Sim</option>
                                    <option value="N">Não</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-text form-group field-quantidade">
                                <label for="quantidade" class="fb-text-label">Quantidade<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="quantidade" value="{{$dadosEmissao['QUANTIDADE']}}" id="quantidade" required="required" aria-required="true">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-text form-group field-valorunitario">
                                <label for="valorunitario" class="fb-text-label">Valor unitário<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="valorunitario" id="valorunitario" required="required" aria-required="true" value="{{$dadosEmissao['TOTAL_C_DESC']}}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-text form-group field-valortotal">
                                <label for="valortotal" class="fb-text-label">Valor total<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="valortotal" id="valortotal" required="required" aria-required="true" value="{{$dadosEmissao['TOTAL_C_DESC']}}">
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="rendered-form">
                <div class="fb-button form-group field-emitir">
                    <button type="submit" class="btn-success btn" name="emitir" style="success" id="emitir">Emitir</button>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
@endsection