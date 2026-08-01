<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BoletoItau extends Model
{
    protected $table = 'TB_BOLETO_ITAU';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $fillable = [
        'NOTAFISCAL',
        'RESPONSE',
        'ID_BENEFICIARIO',
        'CARTEIRA',
        'DATA_INCLUSAO',
        'NOSSO_NUMERO'
    ];
}
