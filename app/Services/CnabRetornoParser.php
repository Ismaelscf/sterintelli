<?php

namespace App\Services;

class CnabRetornoParser
{
    //codigos de ocorrencia do padrao CNAB 400 (Febraban) que representam pagamento
    //efetivo do titulo; qualquer outro codigo (baixa sem pagamento, confirmacao de
    //entrada, protesto, alteracao de vencimento etc) e so informativo
    const CODIGOS_PAGAMENTO = ['06', '15', '17'];

    const DESCRICOES_OCORRENCIA = [
        '02' => 'Confirmação de entrada de título',
        '03' => 'Confirmação de pedido de alteração de juros',
        '04' => 'Confirmação de pedido de baixa',
        '05' => 'Confirmação do pedido de 5 dias corridos',
        '06' => 'Liquidação normal',
        '09' => 'Baixado automaticamente via arquivo',
        '10' => 'Baixado conforme instruções da agência',
        '11' => 'Título em ser (antes do vencimento)',
        '12' => 'Confirmação de abatimento concedido',
        '13' => 'Confirmação de cancelamento de abatimento',
        '14' => 'Confirmação de alteração de vencimento',
        '15' => 'Liquidação em cartório',
        '16' => 'Confirmação de alteração de dados',
        '17' => 'Liquidação após baixa ou título não registrado',
        '19' => 'Confirmação de recebimento de instrução de protesto',
        '20' => 'Confirmação de recebimento de instrução para sustar protesto',
        '23' => 'Remessa a cartório',
        '24' => 'Retirada de cartório e manutenção em carteira',
        '27' => 'Baixa rejeitada',
        '28' => 'Débito de tarifas/custas',
        '30' => 'Alteração de outros dados rejeitada',
    ];

    /**
     * Parseia o conteudo de um arquivo de retorno CNAB 400 (layout padrao Febraban,
     * usado pelo Itau). Retorna um array de registros de detalhe (tipo 1), ignorando
     * header e trailer.
     *
     * @param string $conteudo
     * @param string $nomeArquivo
     * @return array
     */
    public function parse($conteudo, $nomeArquivo = '')
    {
        $linhas = preg_split('/\r\n|\r|\n/', $conteudo);
        $registros = [];

        foreach ($linhas as $numeroLinha => $linha) {
            if (strlen($linha) < 126) {
                continue; //linha em branco/curta demais (ex: ultima linha vazia do arquivo)
            }

            $tipoRegistro = substr($linha, 0, 1);
            if ($tipoRegistro !== '1') {
                continue; //ignora header (0) e trailer (9)
            }

            $numeroNota = trim(substr($linha, 116, 10));
            if ($numeroNota === '') {
                continue;
            }

            $codigoOcorrencia = substr($linha, 108, 2);
            $dataOcorrencia = $this->formataData(substr($linha, 110, 6));
            $dataVencimento = $this->formataData(substr($linha, 146, 6));
            $valorTitulo = $this->formataValor(substr($linha, 152, 13));
            $valorPago = $this->formataValor(substr($linha, 253, 13));
            $dataCredito = $this->formataData(substr($linha, 295, 6));
            $nomePagador = trim(substr($linha, 324, 66));

            $ehPagamento = in_array($codigoOcorrencia, self::CODIGOS_PAGAMENTO, true);

            $registros[] = [
                'arquivo' => $nomeArquivo,
                'linha' => $numeroLinha + 1,
                'numeronota' => $numeroNota,
                'codigo_ocorrencia' => $codigoOcorrencia,
                'descricao_ocorrencia' => self::DESCRICOES_OCORRENCIA[$codigoOcorrencia] ?? ('Código ' . $codigoOcorrencia),
                'data_ocorrencia' => $dataOcorrencia,
                'data_vencimento' => $dataVencimento,
                'valor_titulo' => $valorTitulo,
                'valor_pago' => $valorPago,
                //data de credito costuma vir zerada quando nao houve pagamento; nesse
                //caso usa a data de ocorrencia como data do pagamento
                'data_pagamento' => ($dataCredito && $dataCredito !== '00/00/00') ? $dataCredito : $dataOcorrencia,
                'nome_pagador' => $nomePagador,
                'eh_pagamento' => $ehPagamento,
            ];
        }

        return $registros;
    }

    private function formataData($ddmmaa)
    {
        $ddmmaa = trim($ddmmaa);
        if (strlen($ddmmaa) !== 6 || $ddmmaa === '000000') {
            return null;
        }

        $dia = substr($ddmmaa, 0, 2);
        $mes = substr($ddmmaa, 2, 2);
        $ano = substr($ddmmaa, 4, 2);

        //arquivos CNAB 400 usam ano com 2 digitos; assume seculo 20xx
        return $dia . '/' . $mes . '/20' . $ano;
    }

    private function formataValor($valor13)
    {
        $valor = (int) trim($valor13);
        return round($valor / 100, 2);
    }
}
