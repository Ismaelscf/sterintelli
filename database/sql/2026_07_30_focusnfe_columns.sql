-- Colunas novas em tab_notas_emitidas para suportar a integracao com a Focus NFe
-- (substituindo a emissao via webservice SOAP da Prefeitura de Sao Luis).
--
-- REF_FOCUS    - referencia unica gerada pelo sistema e enviada a Focus NFe
--                (usada para consultar/cancelar a nota depois)
-- STATUS_FOCUS - ultimo status retornado pela Focus (processando_autorizacao,
--                autorizado, erro_autorizacao, cancelado)
-- URL_DANFSE   - link do PDF do DANFSe hospedado pela Focus NFe
-- CAMINHO_XML  - link do XML da NFSe hospedado pela Focus NFe
--
-- Rodar manualmente no Oracle (usuario SCOTT / schema onde vive tab_notas_emitidas).

ALTER TABLE tab_notas_emitidas ADD (
  REF_FOCUS       VARCHAR2(60),
  STATUS_FOCUS    VARCHAR2(30),
  URL_DANFSE      VARCHAR2(500),
  CAMINHO_XML     VARCHAR2(500)
);
