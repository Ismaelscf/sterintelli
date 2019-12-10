@extends('layout.app') @section('content')
 <form action="{{ url('notas/enviar') }}" method="POST">
   @csrf 

 <input type="hidden"   id="tiporps" name="tiporps"  value= "RPS">
 <input type="hidden"   id="situacaorps" name="situacaorps"  value= "N">
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
                        <div class="col-md-4">
                            <div class="fb-text form-group field-cnpj">
                                <label for="cnpj" class="fb-text-label">CNPJ<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="cnpjprestador" id="cnpjprestador" required="required" aria-required="true" value="{{$cnpjprestador}}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fb-text form-group field-im">
                                <label for="im" class="fb-text-label">Inscrição municpal<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="inscricaomunicipalprestador" id="iinscricaomunicipalprestadorm" required="required" aria-required="true" value="{{$inscricaomunicipalprestador}}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fb-select form-group field-ambiente">
                                <label for="ambiente" class="fb-select-label">Ambiente</label>
                                <select class="form-control" name="ambiente" id="ambiente">
                                    <option value="2" selected="true" id="ambiente-0">Homologação</option>
                                    <option value="1" id="ambiente-1">Produção</option>
                                </select>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-6">
                            
                            
                            <div class="fb-text form-group field-razao">
                                <label for="razao" class="fb-text-label">Razão Social<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="razaosocialprestador" id="razaosocialprestador" required="required" aria-required="true" value="{{$razaosocialprestador}}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="fb-text form-group field-token">
                                <label for="token" class="fb-text-label">Token de Envio<span class="fb-required">*</span></label>
                                <input type="password" class="form-control" name="token" id="token" required="required" aria-required="true" value="{{$token}}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            
                            
                            <div class="fb-text form-group field-razao">
                                <label for="serierps" class="fb-text-label">Desc. No Nota<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="serierps" id="serierps" required="required" aria-required="true" value="NF">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="fb-text form-group field-token">
                                <label for="numerorps" class="fb-text-label">No nota<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="numerorps" id="numerorps" required="required" aria-required="true" value="1">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-text form-group field-token">
                                <label for="dataemissaorps" class="fb-text-label">Data nota<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="dataemissaorps" id="dataemissaorps" required="required" aria-required="true" value="2009-11-21T15:30:00">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-text form-group field-token">
                                <label for="serieprestacao" class="fb-text-label">Série<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="serieprestacao" id="serieprestacao" required="required" aria-required="true" value="99">
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
                <div class="card-header">Dados do tomador</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="rendered-form">
                                <div class="fb-text form-group field-cnpjtomador">
                                    <label for="cpfcnpjtomador" class="fb-text-label">CPF/CNPJ do Tomador<span class="fb-required">*</span></label>
                                    <input type="text" class="form-control" name="cpfcnpjtomador" id="cpfcnpjtomador" required="required" aria-required="true" value="00000000000191">
                                </div>

                                <div class="fb-select form-group field-tipologradourotomador">
                                    <label for="tipologradourotomador" class="fb-select-label">Tipo de logradouro do tomador</label>
                                    <select class="form-control" name="tipologradourotomador" id="tipologradourotomador">
                                        <option value="rua" selected="true" id="tipologradourotomador-0">Rua</option>
                                        <option value="av" id="tipologradourotomador-1">Avenida</option>
                                        <option value="travessar" id="tipologradourotomador-2">Travessa</option>
                                    </select>
                                </div>

                                <div class="fb-text form-group field-numeroenderecotomador">
                                    <label for="numeroenderecotomador" class="fb-text-label">Número</label>
                                    <input type="text" class="form-control" name="numeroenderecotomador" id="numeroenderecotomador">
                                </div>

                                <div class="fb-select form-group field-tipobairrotomador">
                                    <label for="tipobairrotomador" class="fb-select-label">Tipo bairro tomador</label>
                                    <select class="form-control" name="tipobairrotomador" id="tipobairrotomador">
                                        <option value="bairro" selected="true" id="tipobairrotomador-0">Bairro</option>
                                        <option value="conjunto" id="tipobairrotomador-1">Conjunto</option>
                                    </select>
                                </div>

                                <div class="fb-select form-group field-cidadetomador">
                                    <label for="cidadetomador" class="fb-select-label">Cidade do tomador</label>
                                    <select class="form-control" name="cidadetomador" id="cidadetomador">
                                        <option value="2111300" selected="true" id="cidadetomador-0">São Luis</option>
                                        <option value="0001219" id="cidadetomador-1">Teresina</option>
                                    </select>
                                </div>

                                <div class="fb-text form-group field-emailtomador">
                                    <label for="emailtomador" class="fb-text-label">E-mail tomador<span class="fb-required">*</span></label>
                                    <input type="text" class="form-control" name="emailtomador" id="emailtomador" required="required" aria-required="true" value="rese@uol.com">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fb-text form-group field-razaotomador">
                                <label for="razaotomador" class="fb-text-label">Razão social do Tomador<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="razaosocialtomador" id="razaosocialtomador" required="required" aria-required="true" value="EMPRESA teste">
                            </div>
                            <div class="fb-text form-group field-logradourotomador">
                                <label for="logradourotomador" class="fb-text-label">Logradouro do tomador</label>
                                <input type="text" class="form-control" name="logradourotomador" id="logradourotomador">
                            </div>
                            <div class="fb-textarea form-group field-complementoenderecotomador">
                                <label for="complementoenderecotomador" class="fb-textarea-label">Complemento</label>
                                <input type="text" class="form-control" class="form-control" name="complementoenderecotomador" id="complementoenderecotomador">
                            </div>
                            <div class="fb-text form-group field-bairrotomador">
                                <label for="bairrotomador" class="fb-text-label">Bairro</label>
                                <input type="text" class="form-control" name="bairrotomador" id="bairrotomador">
                            </div>
                            <div class="fb-text form-group field-ceptomador">
                                <label for="ceptomador" class="fb-text-label">CEP<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="ceptomador" id="ceptomador" required="required" aria-required="true" value="65000000">
                            </div>
                            <div class="fb-text form-group field-emailtomador">
                                    <label for="inscricaomunicipaltomador" class="fb-text-label">Inscrição municipal do tomador<span class="fb-required">*</span></label>
                                    <input type="text" class="form-control" name="inscricaomunicipaltomador" id="inscricaomunicipaltomador" required="required" aria-required="true" value="123456">
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
                <div class="card-header">Dados da nota</div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-md-6">
                                <div class="rendered-form">
                                    <div class="fb-select form-group field-codigoatividade">
                                        <label for="codigoatividade" class="fb-select-label">Código de Atividade<span class="fb-required">*</span></label>
                                        <select class="form-control" name="codigoatividade" id="codigoatividade" required="required" aria-required="true">
                                            <option value="412040000" selected="true">412040000</option>
                                        </select>
                                    </div>
                                    <div class="fb-select form-group field-codigoservico">
                                        <label for="codigoservico" class="fb-select-label">Código do Serviço<span class="fb-required">*</span></label>
                                        <select class="form-control" name="codigoservico" id="codigoservico" required="required" aria-required="true">
                                            <option value="1002" selected="true">1002</option>
                                        </select>
                                    </div>

                                    <div class="fb-select form-group field-tiporecolhimento">
                                        <label for="tiporecolhimento" class="fb-select-label">Tipo de recolhimento<span class="fb-required">*</span></label>
                                        <select class="form-control" name="tiporecolhimento" id="tiporecolhimento" required="required" aria-required="true">
                                            <option value="A" selected="true" id="tiporecolhimento-0">A</option>
                                        </select>
                                    </div>
                                    <div class="fb-select form-group field-operacao">
                                        <label for="operacao" class="fb-select-label">Operação<span class="fb-required">*</span></label>
                                        <select class="form-control" name="operacao" id="operacao" required="required" aria-required="true">
                                            <option value="A" selected="true" id="operacao-0">A</option>
                                        </select>
                                    </div>

                                    <div class="fb-text form-group field-valorpis">
                                        <label for="valorpis" class="fb-text-label">Valor PIS<span class="fb-required">*</span></label>
                                        <input type="text" class="form-control" name="valorpis" value="0" id="valorpis" required="required" aria-required="true">
                                    </div>

                                    <div class="fb-text form-group field-valorinss">
                                        <label for="valorinss" class="fb-text-label">Valor INSS<span class="fb-required">*</span></label>
                                        <input type="text" class="form-control" name="valorinss" value="0" id="valorinss" required="required" aria-required="true">
                                    </div>

                                    <div class="fb-text form-group field-valorcsll">
                                        <label for="valorcsll" class="fb-text-label">Valor CSLL<span class="fb-required">*</span></label>
                                        <input type="text" class="form-control" name="valorcsll" value="0" id="valorcsll" required="required" aria-required="true">
                                    </div>

                                    <div class="fb-text form-group field-aliquotacofins">
                                        <label for="aliquotacofins" class="fb-text-label">Aliquota COFINS<span class="fb-required">*</span></label>
                                        <input type="text" class="form-control" name="aliquotacofins" value="0" id="aliquotacofins" required="required" aria-required="true">
                                    </div>

                                    <div class="fb-text form-group field-aliquotair">
                                        <label for="aliquotair" class="fb-text-label">Aliquota IR<span class="fb-required">*</span></label>
                                        <input type="text" class="form-control" name="aliquotair" value="0" id="aliquotair" required="required" aria-required="true">
                                    </div>

                                </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fb-text form-group field-aliquotaatividade">
                                <label for="aliquotaatividade" class="fb-text-label">Aliquota da atividade<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="aliquotaatividade" id="aliquotaatividade" required="required" aria-required="true" value="5">
                            </div>
                            <div class="fb-select form-group field-municipioprestacao">
                                <label for="municipioprestacao" class="fb-select-label">Municipio de prestacao<span class="fb-required">*</span></label>
                                <select class="form-control" name="municipioprestacao" id="municipioprestacao" required="required" aria-required="true">
                                    <option value="0001219" selected="true" id="municipioprestacao-0">Teresina</option>
                                    <option value="2111300" id="municipioprestacao-1">São Luis</option>
                                </select>
                            </div>
                            <div class="fb-select form-group field-tributacao">
                                <label for="tributacao" class="fb-select-label">Tributação<span class="fb-required">*</span></label>
                                <select class="form-control" name="tributacao" id="tributacao" required="required" aria-required="true">
                                    <option value="T" selected="true" id="tributacao-0">T</option>
                                    <option value="option-2" id="tributacao-1">Option 2</option>
                                </select>
                            </div>

                            <div class="fb-text form-group field-valorcofins">
                                <label for="valorcofins" class="fb-text-label">Valor COFINS<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="valorcofins" value="0" id="valorcofins" required="required" aria-required="true">
                            </div>
                            <div class="fb-text form-group field-valorir">
                                <label for="valorir" class="fb-text-label">Valor IR<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="valorir" value="0" id="valorir" required="required" aria-required="true">
                            </div>
                            <div class="fb-text form-group field-aliquotapis">
                                <label for="aliquotapis" class="fb-text-label">Aliquota PIS<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="aliquotapis" value="0" id="aliquotapis" required="required" aria-required="true">
                            </div>
                            <div class="fb-text form-group field-aliquotainss">
                                <label for="aliquotainss" class="fb-text-label">Aliquota INSS<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="aliquotainss" value="0" id="aliquotainss" required="required" aria-required="true">
                            </div>
                            <div class="fb-text form-group field-aliquotacsll">
                                <label for="aliquotacsll" class="fb-text-label">Aliquota CSSL</label>
                                <input type="text" class="form-control" name="aliquotacsll" value="0" id="aliquotacsll">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">

                            <div class="fb-textarea form-group field-descricaorps">
                                <label for="descricaorps" class="fb-textarea-label">Descrição da Nota<span class="fb-required">*</span></label>
                                <textarea type="textarea" class="form-control" name="descricaorps" rows="3" id="descricaorps" required="required" aria-required="true">SERVIÇOS DE ESTERILIZAÇÃO DE MATERIAIS MÉDICO - HOSPITALARES CONFORME LISTA ANEXA REFERENTE À AGOSTO DE 2018. (VENCIMENTO 11/09/2018). CASO NÃO CONSIGA PAGAR ATÉ O VENCIMENTO ENTRAR EM CONTATO COM A EMPRESA, POIS A NOTA ESTÁ COM REGISTRO EM CARTÓRIO.</textarea>
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
                                    <textarea type="textarea" class="form-control" name="discriminacaoservico" id="discriminacaoservico" required="required" aria-required="true">(SERVIÇOS DE ESTERILIZAÇÃO DE MATERIAIS MEDICO-HOSPITALARES)</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="fb-text form-group field-quantidade">
                                <label for="quantidade" class="fb-text-label">Quantidade<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="quantidade" value="1" id="quantidade" required="required" aria-required="true">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-text form-group field-valorunitario">
                                <label for="valorunitario" class="fb-text-label">Valor unitário<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="valorunitario" id="valorunitario" required="required" aria-required="true" value="100">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-text form-group field-valortotal">
                                <label for="valortotal" class="fb-text-label">Valor total<span class="fb-required">*</span></label>
                                <input type="text" class="form-control" name="valortotal" id="valortotal" required="required" aria-required="true" value="100">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fb-select form-group field-tributavel">
                                <label for="tributavel" class="fb-select-label">Tributável</label>
                                <select class="form-control" name="tributavel" id="tributavel">
                                    <option value="S" selected="true" id="tributavel-0">Sim</option>
                                    <option value="N" id="tributavel-1">Não</option>
                                </select>
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