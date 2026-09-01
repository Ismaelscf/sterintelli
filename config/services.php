<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'itau' => [
        'client_id' => env('ITAU_CLIENT_ID'),
        'client_secret' => env('ITAU_CLIENT_SECRET'),
        'account_key' => env('ITAU_ACCOUNT_KEY'),
        'api_url' => env('ITAU_API_URL'),
    ],

    'focusnfe' => [
        'token' => env('FOCUS_NFE_TOKEN'),
        'ambiente' => env('FOCUS_NFE_AMBIENTE', 'homologacao'), // homologacao | producao
        'url_homologacao' => 'https://homologacao.focusnfe.com.br/v2',
        'url_producao' => 'https://api.focusnfe.com.br/v2',
        // Codigo de tributacao nacional (NFSe Nacional, 6 digitos) do servico prestado.
        // 070901 = Varricao, coleta e remocao de lixo, rejeitos e outros residuos quaisquer.
        'codigo_servico_nacional' => env('FOCUS_NFE_CODIGO_SERVICO', '070901'),
        // Codigo NBS (Nomenclatura Brasileira de Servicos) correspondente ao servico prestado.
        // 123019900 = Outros servicos de saude humana nao classificados em subposicoes anteriores.
        'codigo_nbs' => env('FOCUS_NFE_CODIGO_NBS', '123019900'),
        // Codigo indicador da operacao de fornecimento (tabela do governo). 030101 = Servico
        // prestado fisicamente sobre a pessoa ou fruido presencialmente por pessoa fisica -
        // estabelecimento do fornecedor como local da prestacao.
        'codigo_indicador_operacao' => env('FOCUS_NFE_CODIGO_INDICADOR_OPERACAO', '030101'),
        // Codigo de Situacao Tributaria do IBS/CBS. 200 = Aliquota reduzida.
        'ibs_cbs_situacao_tributaria' => env('FOCUS_NFE_IBS_CBS_CST', '200'),
        // Codigo de Classificacao Tributaria do IBS/CBS. 200029 = Fornecimento dos servicos
        // de saude humana (Anexo III).
        'ibs_cbs_classificacao_tributaria' => env('FOCUS_NFE_IBS_CBS_CCLASSTRIB', '200029'),
        // Codigo IBGE (7 digitos) do municipio do prestador/emissor (Sao Luis - MA).
        // Fixo pois a empresa so emite nesse municipio.
        'codigo_municipio_prestador' => env('FOCUS_NFE_CODIGO_MUNICIPIO_PRESTADOR', '2111300'),
    ],

];
