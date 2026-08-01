<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class FocusNfeService
{
    protected $client;

    public function __construct()
    {
        $ambiente = config('services.focusnfe.ambiente', 'homologacao');
        $baseUrl = $ambiente === 'producao'
            ? config('services.focusnfe.url_producao')
            : config('services.focusnfe.url_homologacao');

        $this->client = new Client([
            'base_uri' => rtrim($baseUrl, '/') . '/',
            'auth' => [config('services.focusnfe.token'), ''],
            'timeout' => 30,
        ]);
    }

    public function emitir($ref, array $payload)
    {
        return $this->request('POST', 'nfsen', [
            'query' => ['ref' => $ref],
            'json' => $payload,
        ]);
    }

    public function consultar($ref)
    {
        return $this->request('GET', 'nfsen/' . $ref);
    }

    public function cancelar($ref, $justificativa)
    {
        return $this->request('DELETE', 'nfsen/' . $ref, [
            'json' => ['justificativa' => $justificativa],
        ]);
    }

    protected function request($method, $uri, array $options = [])
    {
        try {
            $response = $this->client->request($method, $uri, $options);

            return [
                'http_status' => $response->getStatusCode(),
                'body' => json_decode((string) $response->getBody(), true),
            ];
        } catch (RequestException $e) {
            if (!$e->hasResponse()) {
                throw $e;
            }

            $response = $e->getResponse();

            return [
                'http_status' => $response->getStatusCode(),
                'body' => json_decode((string) $response->getBody(), true),
            ];
        }
    }
}
