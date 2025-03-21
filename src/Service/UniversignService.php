<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class UniversignService
{
    private string $universignApiUrl;
    private string $apiKey;
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient, string $universignApiUrl, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->universignApiUrl = $universignApiUrl;
        $this->apiKey = $apiKey;
    }

    public function createTransaction(): array
    {
        $response = $this->httpClient->request('POST', "{$this->universignApiUrl}/v1/transactions", [
            'auth_basic' => [$this->apiKey, ''],
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['name' => 'Signature de document', 'language' => 'fr'],
        ]);

        return $response->toArray();
    }
}
