<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cobranca extends Model
{
    protected $table = 'ENVIO_EMAIL_COBRANCA';

    public $timestamps = false;

    protected $fillable = [
        'CODIGO_CLIENTE',
        'NOME_CLIENTE',
        'EMAIL_CLIENTE',
        'NF',
        'VALOR',
        'DATA_ENVIO'
    ];
}
