-- Corrige o tipo da coluna NOTAFISCAL em TB_BOLETO_ITAU: era NUMBER, so aceitava
-- numeronota puramente numerico (formato antigo, anterior a Focus NFe). O numeronota
-- atual tem letras (NAC2478, STER000001 etc), entao salvar um boleto pra uma nota
-- desse formato provavelmente falha hoje com erro de conversao numerica do Oracle.
--
-- Oracle nao deixa mudar o tipo de uma coluna com dados direto (ORA-01439), entao o
-- caminho e: cria coluna nova (VARCHAR2), copia os dados convertendo pra texto, remove
-- a coluna antiga, renomeia a nova pro nome original. Testado em ambiente local com os
-- dados reais (varias centenas de linhas) sem perda de dado.
--
-- Rodar manualmente no Oracle (usuario SCOTT / schema onde vive TB_BOLETO_ITAU).

ALTER TABLE TB_BOLETO_ITAU ADD (NOTAFISCAL_NOVO VARCHAR2(30));

UPDATE TB_BOLETO_ITAU SET NOTAFISCAL_NOVO = TO_CHAR(NOTAFISCAL);

ALTER TABLE TB_BOLETO_ITAU DROP COLUMN NOTAFISCAL;

ALTER TABLE TB_BOLETO_ITAU RENAME COLUMN NOTAFISCAL_NOVO TO NOTAFISCAL;
