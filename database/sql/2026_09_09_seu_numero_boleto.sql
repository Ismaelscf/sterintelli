-- Coluna nova em tab_notas_emitidas para o "seu numero" curto usado na geracao de
-- boletos (Itau), formato STER000001 (10 caracteres).
--
-- Antes o "texto_seu_numero" enviado a Itau usava o numeronota inteiro (pode passar
-- de 20 caracteres, ex "STE1869403-20260901152433"), mas o arquivo de retorno CNAB 400
-- do banco so tem 10 posicoes pra esse campo, entao chegava cortado - dificultando (ou
-- ate impossibilitando, em caso de ambiguidade) localizar a nota certa na tela de Baixa.
--
-- SEU_NUMERO_BOLETO guarda o codigo curto gerado (NotaFiscalRepository::
-- proximoSeuNumeroBoleto), pra a tela de Baixa conseguir localizar a nota mesmo quando
-- o retorno do banco traz esse codigo em vez do numeronota.
--
-- Rodar manualmente no Oracle (usuario SCOTT / schema onde vive tab_notas_emitidas).

ALTER TABLE tab_notas_emitidas ADD (
  SEU_NUMERO_BOLETO VARCHAR2(10)
);
