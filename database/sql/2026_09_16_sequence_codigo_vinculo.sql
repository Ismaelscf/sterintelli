-- 3. Trigger: rede de seguranca, so entra em acao se algum INSERT nao informar o
-- codigo (o fluxo normal via PHP ja gera e informa explicitamente antes de inserir,
-- pois precisa do codigo ANTES de chamar a Focus NFe)
--
-- OBS: o NEXTVAL precisa vir de um SELECT ... INTO ... FROM DUAL, e nao de atribuicao
-- direta (v := SEQ...NEXTVAL). Nesse Oracle (10g XE 10.2.0.1) atribuicao direta dentro
-- de trigger da erro de compilacao PLS-00357 ("Table,View Or Sequence reference not
-- allowed in this context").
CREATE OR REPLACE TRIGGER TRG_SEU_NUMERO_BOLETO
BEFORE INSERT ON tab_notas_emitidas
FOR EACH ROW
DECLARE
  v_proximo NUMBER;
BEGIN
  IF :NEW.SEU_NUMERO_BOLETO IS NULL THEN
    SELECT SEQ_CODIGO_VINCULO.NEXTVAL INTO v_proximo FROM DUAL;
    :NEW.SEU_NUMERO_BOLETO := 'STER' || LPAD(v_proximo, 6, '0');
  END IF;
  IF :NEW.DTA_CADASTRO_VINCULO IS NULL THEN
    :NEW.DTA_CADASTRO_VINCULO := SYSDATE;
  END IF;
END;
/
