<?php

namespace App;

require (__DIR__.'/tcpdf_config.php');

use TCPDF;

//
// Configura de cabeçalho e rodapé baseado nas informações da filial
//
class Config {
  public function __construct ($name='Relátorio', $filialId=null, $subject=null, $fast=1, $orientation=PDF_PAGE_ORIENTATION) {
    /*if ($filial) {
      $filial = pdoGet('filial', $filialId);
      $emitente = pdoGet('parceiro', $filial->parceiro_id);
      $municipio = pdoGet('municipio', $emitente->municipio_id);
      $uf = pdoGet('uf', $municipio->uf_id);
    }*/
    $this->name = $name;
    $this->subject = $subject ? $subject : $name;
    $this->orientation = $orientation;
    $this->keywords = 'report';
    //$this->logo = UPLOAD_PATH.$filial->logo;
    $this->logo = '';
    $this->logoWidth = '15';
    $this->title = '-';
    $this->subtitle = '-';

  }
}


//
// Report
//
class Report extends TCPDF {
  public function __construct ($config=null) {
    if ($config === null)
      $config = new Config();

    parent::__construct($config->orientation, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $this->orientation = $config->orientation;
    $this->subject = $config->subject;

    $this->boxHeight = 7;
    $this->fontCaption = ['helvetica', '', 6];
    $this->fontText = ['helvetica', 'B', 8];

    $this->SetCreator(PDF_CREATOR);
    $this->SetAuthor(PDF_AUTHOR);
    $this->SetTitle($config->name);
    $this->SetSubject($config->subject);
    $this->SetKeywords($config->keywords);

    $this->SetHeaderData($config->logo, $config->logoWidth, $config->title, $config->subtitle);

    $this->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $this->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $this->SetHeaderMargin(PDF_MARGIN_HEADER);
    $this->SetFooterMargin(PDF_MARGIN_FOOTER);

    $this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
  }

  public function __destruct() {
    // se retirar, apagará a logo após impressao
    $this->_destroy(false);
  }  

  public function Box ($w, $caption, $text, $ln=0, $border=1, $align='L', $h=null, $font=null, $valign='B', $fill=false, $fontCaption=null, $captionAlign='L')
  {
    $x = $this->GetX();
    if ($text === null)
      $text = '';
    if ($h === null)
      $h = $this->boxHeight;
    if ($h <= 0)
      $text = "\n$text";

    $this->SetFont(...$this->fontCaption);
    $this->SetCellPaddings(0.5, 0, 2, 1);
    if ($caption) {

      if (!$fontCaption)
           $fontCaption = $this->fontText;
      $this->SetFont(...$fontCaption);
      $this->MultiCell($w, $h, $caption, 0, $captionAlign, $fill, 0, '', '', true, false, false, true, $h, 'T', false);
      $this->SetX($x);
    }
    if (!$font)
      $font = $this->fontText;
    $this->SetFont(...$font);
    $this->MultiCell($w, $h, $text, $border, $align, $fill, $ln, '', '', true, false, false, true, $h, $valign, false);
  }

  function Columns (Array $positions, Array $rows, Array $fontCaption=null, Array $fontText=null, int $h=5) {
    $posFirst = [];
    $posCaption = [];
    $ys = [];
    $padd = $this->getCellPaddings();
    $this->SetCellPadding(0);

    if (!$fontCaption)
      $fontCaption = $this->fontCaption;
    if (!$fontText)
      $fontText = $this->fontText;

    // imprimir captions
    foreach ($positions as $keyP => $pos) {
      foreach ($rows as $keyR => $row) {
        foreach ($row as $keyI => $item) {

          if (in_array($keyI, ['h', 'fontCaption', 'fontText'], true))
            continue;

          if (array_key_exists('fontCaption',$row))
            $font = $row['fontCaption'];
          else
            $font = $fontCaption;
          $this->SetFont(...$font);

          if ($keyP == $keyI) {

            [$caption, $text] = $item;


            if (array_key_exists($keyR,$ys))
              $this->SetY($ys[$keyR]);
            else
              $ys[$keyR] = $this->GetY();

            $this->SetX($pos);

            if (array_key_exists('h',$row))
              $h1 = $row['h'];
            else
              $h1 = $h;

            $this->MultiCell(0, $h1, $caption, 0, 'L', false, 1, '', '', true, false, false, true, $h1, 'T', false);
            $w = $this->GetStringWidth($caption);

            if (array_key_exists($keyP, $posFirst))
                $posFirst[$keyP] = max($posFirst[$keyP], $w);
            else
                $posFirst[$keyP] = $w;

            $posCaption[$caption] = $w;
          }
        }
      }
    }

    // imprimir text
    $first = true;
    foreach ($positions as $keyP => $pos) {
      foreach ($rows as $keyR => $row) {
        foreach ($row as $keyI => $item) {
          if (in_array($keyI, ['h', 'fontCaption', 'fontText'], true))
            continue;

          if (array_key_exists('fontText',$row))
            $font = $row['fontText'];
          else
            $font = $fontText;

          $this->SetFont(...$font);
  
          if ($keyP == $keyI) {
            [$caption, $text] = $item;
            $this->SetY($ys[$keyR]);
            if ($first)
              $this->SetX($pos + $posFirst[$keyP] + 1);
            else
              $this->SetX($pos + $posCaption[$caption] + 1);

            $nextItem = next($row);
            $overflow = false;
            if ($nextItem && $nextItem != $item) {
              $nextKey = array_search ($nextItem, $row);
              if ($item == end($row)) {
                $w = $this->w - $this->rMargin - $this->GetX() - 2;
                $xOF = $this->w - $this->rMargin - $this->GetStringWidth('...');
              } else {
                $w = $positions[$nextKey] - $this->GetX() - 3;
                $xOF = $positions[$nextKey] - $this->GetStringWidth('...') - 1;
              }
              
              if ($this->GetStringWidth($text) > $w){
                $overflow = true;
              }
            } else
              $w = 0;

            if (array_key_exists('h',$row))
              $h1 = $row['h'];
            else
              $h1 = $h;

            $this->MultiCell($w, $h1, $text, 0, 'L', false, 0, '', '', true, false, false, true, $h1, 'T', false);

            //COMENTADO PARA AVALIAR DEPOIS.
            //if ($overflow)
              //$this->Text($xOF, $this->GetY(), '...');
          }
        }
      }
      $first = false;
    }

  }
}
