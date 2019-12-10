<?php

require_once 'report.php';

$conn = new PDO('mysql:host=localhost;dbname=erp;charset=utf8', 'admin', '123');


function pdoGet ($table, $id) {
  global $conn;
  $rs = $conn->prepare("SELECT * FROM $table WHERE id=$id");
  $rs->execute();
  return $rs->fetch(PDO::FETCH_OBJ);
}


function danfse ($notaId, $target) {

  $nota = pdoGet('nfse', $notaId);
  $fatura = pdoGet('fatura', $nota->fatura_id);
  $filial = pdoGet('filial', $fatura->filial_id);

  $emitente = pdoGet('parceiro', $filial->parceiro_id);
  $emitMun = pdoGet('municipio', $emitente->municipio_id);
  $emitUF = pdoGet('uf', $emitMun->uf_id);

  $tomador = pdoGet('parceiro', $fatura->parceiro_id);
  $tomadorMun = pdoGet('municipio', $tomador->municipio_id);
  $tomadorUF = pdoGet('uf', $tomadorMun->uf_id);

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
    "Recebemos de $emitente->nome os serviços \nconstantes da NOTA FISCAL DE SERVIÇOS ELETRÔNICA indicada ao lado",
    0, 'TBR', 'L', 0, ['helvetica', '', 7], 'T'
  );
  $col2 = $pdf->GetX();
  $pdf->Ln();
  $pdf->Box(35, 'Data de recebimento', '', 0, 'BR', 'L', 9);
  $pdf->Box(100, 'Identificação e assinatura do recebedor', '', 1, 'BR', 'L', 9);
  $y1 = $pdf->GetY();
  $pdf->SetY($y);
  $pdf->SetX($col2);
  $pdf->Box(0, '', "NFS-e\nNº " . sprintf('%06d', $nota->numero), 1, 'TB', 'C', $y1-$y, ['helvetica', 'B', 10], 'M');
  $pdf->Ln(10);

  //--------------------------------------
  // Cabeçalho
  //--------------------------------------
  $y = $pdf->GetY();
  $pdf->Image(BACKEND . 'images/ctba.jpg', 25, $y+2, 26, 26);
  $pdf->Box(135, '', '', 0, 'TLBR', 'C', 30);
  $pdf->SetFont('helvetica', 'B', 12);
  $pdf->Text(60, $y+2, 'PREFEITURA MUNICIPAL DE CURITIBA', false, false, true, 0, 1);
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Text(65, $pdf->GetY()+1, 'SECRETARIA MUNICIPAL DE FINANÇAS', false, false, true, 0, 1);
  $pdf->SetFont('helvetica', 'B', 11);
  $pdf->Text(52, $pdf->GetY()+1, 'NOTA FISCAL DE SERVIÇOS ELETRÔNICA - NFS-e', false, false, true, 0, 1);
  $pdf->SetFont('helvetica', '', 8);
  $pdf->Text(62, $pdf->GetY()+1,
    "RPS nº $nota->numeroRps, Série $nota->serie, emitido em " . ymdDmy($nota->emissaoRps) . ' às ' . ymdH($nota->emissaoRps)
  );

  $pdf->SetY($y);
  $pdf->SetX($col2);
  $pdf->Box(0, 'Número da nota', sprintf('%06d', $nota->numero), 1, 'TBR', 'C', 10, ['helvetica', 'B', 10]);
  $pdf->SetX($col2);
  $pdf->Box(0, 'Data e hora da emissão', ymdDmyH($nota->emissao), 1, 'TBR', 'C', 10, ['helvetica', 'B', 9]);
  $pdf->SetX($col2);
  $pdf->Box(0, 'Código de verificação', $nota->verificacao, 1, 'TBR', 'C', 10, ['helvetica', 'B', 10]);

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
  $pdf->Box(0, '', 'VALOR TOTAL DA NOTA = R$ ' . numberFormat($nota->total), 1, 'BLR', 'C', 6, ['helvetica', 'B', 9]);
  $pdf->Box(0, 'Código de atividade', '1401 - ajdksf akjsfdhkah', 1, 'BLR', 'L');
  $pdf->Box(38, 'Valor total das deduções', '0.00', 0, 'BLR', 'C', 8);
  $pdf->Box(38, 'Base de cálculo (R$)', numberFormat($nota->total), 0, 'BLR', 'C', 8);
  $pdf->Box(18, 'Alíquota (%)', '0.00', 0, 'BLR', 'C', 8);
  $pdf->Box(38, 'Valor do ISS (R$)', '0.00', 0, 'BLR', 'C', 8);
  $pdf->Box(0, 'Crédito p/ abatimento do IPTU', numberFormat($nota->valor * ($filial->aliqCreditoIptu / 100)),
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
    'Para verificar a autenticidade desta NFS-e acesse: hhtp://isscuritiba.curitiba.pr.gov.br/portalnfse/autenticidade.aspx',
    0, 'C', false, 1
  );

  //--------------------------------------
  // Prestador
  //--------------------------------------
  $p = $emitente;
  $endereco = "$p->logradouro, $p->numero $p->complemento - $p->bairro - $p->cep";
  $pdf->Image(UPLOAD_PATH . $filial->logo, 25, $yP+2, 26, 26);
  $pdf->SetY($yP+10);
  $pdf->Columns([0 => 52, 5 => 100, 10 => 120, 15 => 150], [
    ['h' => 5, 0 => ['Razão social:', $p->nome]], 
    ['h' => 5, 0 => ['CPF/CNPJ:', $p->cnpj], 10 => ['Inscrição Municipal:', $p->inscMunicipal]],
    ['h' => 5, 0 => ['Endereço:', $endereco], 15 => ['Fone:', $p->telefone]],
    // bug: na ultima linha o 'h' sempre dispara text overflow ...
    [          0 => ['Município:', $emitMun->nome], 5 => ['UF:', $emitUF->sigla], 10 => ['Email:' , $p->email]]
  ], ['helvetica', 'B', 8], null, 5);

  //--------------------------------------
  // Tomador
  //--------------------------------------
  $p = $tomador;
  $endereco = "$p->logradouro, $p->numero $p->complemento - $p->bairro - $p->cep";
  $pdf->SetY($yT+10);
  $pdf->Columns([0 => 25, 5 => 100, 10 => 120, 15 => 150], [
    [0 => ['Razão social:', $p->nome]], 
    [0 => ['CPF/CNPJ:', $p->cnpj], 10 => ['Inscrição Municipal:', $p->inscMunicipal]],
    [0 => ['Endereço:', $endereco], 15 => ['Fone:', $p->telefone]],
    [0 => ['Município:', $tomadorMun->nome], 5 => ['UF:', $tomadorUF->sigla], 10 => ['Email:' , $p->email]]
  ], ['helvetica', 'B', 8]);

  //--------------------------------------
  // Descriminação
  //--------------------------------------
  $pdf->SetY($yD+10);
  $pdf->Box(0, '', $nota->discriminacao, 1, 0, 'L', 70, ['helvetica', '', 8], 'T');

  // $txtPdf = $pdf->Output('nfse.pdf', 'S');
  // file_put_contents(UPLOAD_PATH . $target, $txtPdf);
  
  echo $pdf->Output('nfse.pdf', 'I');

}

?>
