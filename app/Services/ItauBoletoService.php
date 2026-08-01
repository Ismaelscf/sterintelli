<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Repositories\BoletoItauRepository;
use FFI\Exception;

class ItauBoletoService
{
    protected $clientId;
    protected $clientSecret;
    protected $accountKey;
    protected $apiUrl;
    protected $certPath;
    protected $keyPath;
    protected $accessToken;
    protected $itauBoletoRepository;

    public function __construct(BoletoItauRepository $itauBoletoRepository)
    {
        $this->clientId = config('services.itau.client_id');
        $this->clientSecret = config('services.itau.client_secret');
        $this->accountKey = config('services.itau.account_key');
        $this->apiUrl = config('services.itau.api_url');
        $this->certPath = config('services.itau.cert_path');
        $this->keyPath = config('services.itau.key_path');
        $this->itauBoletoRepository = $itauBoletoRepository;
    }

    public function authenticate()
    {
        $curl = curl_init();

        $client_id = $this->clientId;
        $client_secret = $this->clientSecret;
        $path_private_key = 'c:/certificado/itau/certificado.pem';
        $password = '';

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://sts.itau.com.br/api/oauth/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'client_credentials',
                'client_id' => $client_id,
                'client_secret' => $client_secret,
            ]),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded"
            ),
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSLCERT => $path_private_key,
            CURLOPT_SSLCERTPASSWD => $password,
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
        }

        curl_close($curl);

        // Verificar e mostrar a resposta para debug
        // dd($response, isset($error_msg) ? $error_msg : null);

        $result = json_decode($response, true);

        if (isset($result['access_token'])) {
            $this->accessToken = $result['access_token'];
        } else {
            throw new \Exception('Erro ao autenticar na API do Itaú: ' . $response);
        }
    }  

    public function emitirBoleto($dadosBoleto)
    {
        $this->authenticate();
        $producao = "https://api.itau.com.br/cash_management/v2";
        $homologacao = "https://sandbox.devportal.itau.com.br/itau-ep9-gtw-cash-management-ext-v2/v2";

        $url = "https://api.itau.com.br/cash_management/v2/boletos";
        // dd($url);
        $postData = json_encode([
            'data' => $dadosBoleto,
        ]);

        $ch = curl_init($url);
        $path_private_key = 'c:/certificado/itau/certificado.pem';
        $password = '';
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
            'x-itau-apikey: 62ac9962-892e-43ff-9435-899bcfbe1890',
            'x-itau-correlationID: ' . $this->generateGUID(),
            'x-itau-flowID: ' . $this->generateGUID(),
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $path_private_key = 'c:/certificado/itau/certificado.pem';
        $password = '';

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSLCERT, $path_private_key);
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $password);

        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
        }

        curl_close($ch);

        // dd($response, $httpStatus);

        if ($httpStatus == 200) {
            return json_decode($response, true);
        } else {
            throw new \Exception('Erro ao emitir boleto: ' . $response);
        }
    }

    private function generateGUID()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double)microtime()*10000); // optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45); // "-"
            $uuid = chr(123)// "{"
                .substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid, 12, 4).$hyphen
                .substr($charid, 16, 4).$hyphen
                .substr($charid, 20, 12)
                .chr(125); // "}"
            return $uuid;
        }
    }

    public function salvarDadosBoleto($id_beneficiario, $nossoNumero, $numeronota)
    {
        try {
            $salvar = $this->itauBoletoRepository->salvarDadosBoleto($id_beneficiario, $nossoNumero, $numeronota);
            return ['message' => 'Dados do boleto salvos com sucesso.'];
        } catch (Exception $e) {
            return ['error' => 'Erro ao salvar os dados do boleto: ' . $e->getMessage()];
        }
    }
    
    public function consultarBoletosporNF($nf) {
        $boletos = $this->itauBoletoRepository->consultarBoletosporNF($nf);
    
        foreach ($boletos as $boleto) {
            try {
                $itau = $this->consultarBoleto($boleto->nosso_numero);
                if (isset($itau['data'][0]['dado_boleto']['dados_individuais_boleto'][0])) {
                    $dadosIndividuais = $itau['data'][0]['dado_boleto']['dados_individuais_boleto'][0];
                    $boleto->status_boleto = $dadosIndividuais['situacao_geral_boleto'] ?? null;
                    $boleto->situacao_vencimento = $dadosIndividuais['status_vencimento'] ?? null;
                } else {
                    $boleto->status_boleto = null;
                    $boleto->situacao_vencimento = null;
                }
            } catch (\Exception $e) {
                $boleto->status_boleto = null;
                $boleto->situacao_vencimento = null;
            }
        }
    
        return $boletos;
    } 

    public function consultarBoleto($nossoNumero)
    {
        $dados = $this->itauBoletoRepository->buscarDadosBoleto($nossoNumero);
        // dd($dados);
        if (is_null($dados)) {
            throw new \Exception('Dados do boleto não encontrados para o nosso número: ' . $nossoNumero);
        }
    
        $this->authenticate();
    
        $producao = "https://secure.api.cloud.itau.com.br/boletoscash/v2";
        $homologacao = "https://sandbox.devportal.itau.com.br/itau-ep9-gtw-cash-management-ext-v2/v2";
    
        $url = $producao . "/boletos?";
        $queryParams = http_build_query([
            'id_beneficiario' => $dados->id_beneficiario,
            'codigo_carteira' => $dados->carteira,
            'nosso_numero' => $dados->nosso_numero,
            'view' => 'full', // ou 'full', dependendo da visão que você deseja
        ]);
    
        $url .= $queryParams;
    
        $ch = curl_init($url);
        $path_private_key = 'c:/certificado/itau/certificado.pem';
        $password = '';
    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
            'x-itau-apikey: 62ac9962-892e-43ff-9435-899bcfbe1890',
            'x-itau-correlationID: ' . $this->generateGUID(),
            'x-itau-flowID: ' . $this->generateGUID(),
        ]);
    
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSLCERT, $path_private_key);
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $password);
    
        // Explicitamente define a requisição como GET
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    
        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            throw new \Exception('Erro de cURL: ' . $error_msg);
        }
    
        curl_close($ch);
        // dd($response);
    
        if ($httpStatus == 200) {
            return json_decode($response, true);
        } else {
            throw new \Exception('Erro ao consultar boleto: ' . $response);
        }
    }
    
    public function alterarVencimento($idBoleto, $novaDataVencimento)
    {
        $this->authenticate();
        
        $producao = "https://secure.api.cloud.itau.com.br/boletoscash/v2";
        $url = $producao . "/boletos/" . $idBoleto . "/data_vencimento";
    
        $data = [
            'data_vencimento' => $novaDataVencimento,
        ];
    
        $ch = curl_init($url);
        $path_private_key = 'c:/certificado/itau/certificado.pem';
        $password = '';
    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
            'x-itau-apikey: ' . getenv('ITAU_API_KEY'),
            'x-itau-correlationID: ' . $this->generateGUID(),
            'x-itau-flowID: ' . $this->generateGUID(),
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSLCERT, $path_private_key);
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $password);
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            throw new \Exception('Erro de cURL: ' . $error_msg);
        }
    
        curl_close($ch);
        dd($response, $httpStatus);
    
        if ($httpStatus == 204 || $httpStatus == 202) {
            return true;
        } elseif ($httpStatus == 200) {
            return json_decode($response, true);
        } else {
            throw new \Exception('Erro ao alterar a data de vencimento: ' . $response . ' (HTTP Status: ' . $httpStatus . ')');
        }
    }
    
    public function instruirBoleto($nossoNumero, $instrucoes)
    {
        $this->authenticate();

        $response = Http::withToken($this->accessToken)
            ->put($this->apiUrl . '/boletos/' . $nossoNumero, [
                'account_key' => $this->accountKey,
                'data' => $instrucoes,
            ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            throw new \Exception('Erro ao instruir boleto: ' . $response->body());
        }
    }

    public function consultarBoletosEmitidos($filters)
    {
        $this->authenticate();

        $response = Http::withToken($this->accessToken)
            ->get($this->apiUrl . '/boletos', [
                'account_key' => $this->accountKey,
                'filters' => $filters,
            ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            throw new \Exception('Erro ao consultar boletos emitidos: ' . $response->body());
        }
    }

    public function getLastNumero(){
        $nossoNumero = $this->itauBoletoRepository->getLastNumero();
        return $nossoNumero;
    }
}
