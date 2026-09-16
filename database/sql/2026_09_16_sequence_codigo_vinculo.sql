-- Corrige a geracao do codigo de vinculo (STER000001 etc, usado como ref na Focus NFe
-- e como "seu numero" do boleto). Antes era calculado por MAX(...)+1 em PHP, que tem
-- uma race condition real: duas emissoes quase simultaneas podem ler o mesmo "maior
-- numero atual" antes de qualquer uma salvar, e as duas calculam o MESMO proximo
-- codigo - foi isso que causou o erro "codigo ja utilizado" na Focus NFe.
--
-- A partir de agora, o codigo e gerado por uma SEQUENCE do Oracle (NEXTVAL e atomico
-- de verdade, garantido pelo banco, sem essa corrida), mais duas redes de seguranca:
-- uma trigger que preenche automaticamente qualquer INSERT que esquecer de informar
-- o codigo, e uma constraint UNIQUE que bloqueia de vez qualquer duplicata, caso
-- aconteça por algum outro motivo.
--
-- O bloco abaixo calcula o ponto de partida da sequence dinamicamente: nunca abaixo de
-- 2000 (numero definido para dar margem segura acima de qualquer coisa que ja possa
-- ter sido gerada), e sempre acima do maior STERxxxxxx que ja existir na tabela.
--
-- Rodar manualmente no Oracle (usuario SCOTT / schema onde vive tab_notas_emitidas).

-- 1. Sequence
DECLARE
  v_max   NUMBER;
  v_start NUMBER;
BEGIN
  SELECT NVL(MAX(TO_NUMBER(SUBSTR(SEU_NUMERO_BOLETO, 5))), 0) INTO v_max
  FROM tab_notas_emitidas
  WHERE SEU_NUMERO_BOLETO LIKE 'STER%';

  v_start := GREATEST(v_max + 1, 2000);

  EXECUTE IMMEDIATE 'CREATE SEQUENCE SEQ_CODIGO_VINCULO START WITH ' || v_start || ' INCREMENT BY 1 NOCACHE NOCYCLE';
END;
/

-- 2. Coluna de data de cadastro do vinculo (quando o codigo foi gerado pra essa nota)
ALTER TABLE tab_notas_emitidas ADD (DTA_CADASTRO_VINCULO DATE);

-- 3. Trigger: rede de seguranca, so entra em acao se algum INSERT nao informar o
-- codigo (o fluxo normal via PHP ja gera e informa explicitamente antes de inserir,
-- pois precisa do codigo ANTES de chamar a Focus NFe)
CREATE OR REPLACE TRIGGER TRG_SEU_NUMERO_BOLETO
BEFORE INSERT ON tab_notas_emitidas
FOR EACH ROW
BEGIN
  IF :NEW.SEU_NUMERO_BOLETO IS NULL THEN
    :NEW.SEU_NUMERO_BOLETO := 'STER' || LPAD(SEQ_CODIGO_VINCULO.NEXTVAL, 6, '0');
  END IF;
  IF :NEW.DTA_CADASTRO_VINCULO IS NULL THEN
    :NEW.DTA_CADASTRO_VINCULO := SYSDATE;
  END IF;
END;
/

-- 4. Trava definitiva: nunca mais permite duas notas com o mesmo codigo
ALTER TABLE tab_notas_emitidas ADD CONSTRAINT UQ_SEU_NUMERO_BOLETO UNIQUE (SEU_NUMERO_BOLETO);
