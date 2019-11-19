<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItensNota extends Model
{
    protected $table = 'st_central.itens_nota';
	const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'last_update';

     protected $fillable = [
     		'id_nota',
		    'discriminacaoservico',
		    'quantidade',
		    'valorunitario',
		    'valortotal',
		    'tributavel'
     ];
}
