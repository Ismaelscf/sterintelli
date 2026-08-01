<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{

	protected $table = 'CLIENTES';
	protected $primaryKey = 'CODIGO';
    public $timestamps = false;
 
    protected $fillable = [
    	'NOME',
    	'LOJA',
	    'ENDERECO',
        'BAIRRO',
        'MUNICIPIO',
        'UF',
        'CEP',
        'TELEFONE',
		'FAX',
		'NASCIMENTO',
		'EMAIL',
		'OBS',
		'CNPJ',
		'FANTASIA',
		'DESCONTO',
		'COMPUTADOR',
		'IM',
		'IE',
		'NUMERO',
		'ESTADO',
		'MSGNF',
		'COD_TIPO_CLIENTE',
		'COD_ESPECIALIDADE',
		'BOL_ATIVO',
		'DATA_INATIVIDADE',
		'TAXA_TRANSPORTE',
		'MUNICIPIO_ID',
		'ANOTACOES',
		'DT_INI_CONTRATO',
		'DT_FIM_CONTRATO',
		'TIPO_RECOLHIMENTO',
		'DADOS_CONTRATO',
		'COBRANCA_POR_CICLO',
		'VAL_CICLO',
		'BOL_CLIENTE_INDUSTRIA',
		'BOL_CLIENTE_P_PACIENTE',
		'BOL_CLIENTE_P_CIRURGIA',
		'ATUALIZACAO',
		'PER_IR',
		'BOL_REMOVER_IMPOSTOS_NF',
		'PUBLICA',
		'SENHA'
    ];
}
