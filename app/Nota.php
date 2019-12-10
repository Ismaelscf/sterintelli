<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{

	//protected $table = 'st_central.notas';
	protected $table = 'notas';
	const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'last_update';

    protected $fillable = [
        'inscricaomunicipalprestador',
		'razaosocialprestador',
		'tiporps',
		'serierps',
		'numerorps',
		'dataemissaorps',
		'situacaorps',
		'serieprestacao',
		'inscricaomunicipaltomador',
		'cpfcnpjtomador',
		'razaosocialtomador',
		'tipologradourotomador',
		'logradourotomador',
		'numeroenderecotomador',
		'complementoenderecotomador',
		'tipobairrotomador',
		'bairrotomador',
		'cidadetomador',
		'cidadetomadordescricao',
		'ceptomador',
		'emailtomador',
		'codigoatividade',
		'codigoservico',
		'aliquotaatividade',
		'tiporecolhimento',
		'municipioprestacao',
		'municipioprestacaodescricao',
		'operacao',
		'tributacao',
		'valorpis',
		'valorcofins',
		'valorinss',
		'valorir',
		'valorcsll',
		'aliquotapis',
		'aliquotacofins',
		'aliquotainss',
		'aliquotair',
		'aliquotacsll', 'descricaorps', 'last_update', 'creation_date'
    ];
}
