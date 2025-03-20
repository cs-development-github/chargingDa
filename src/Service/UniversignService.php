<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class UniversignService
{
    private HttpClientInterface $httpClient;
    private string $universignApiUrl;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $universignApiUrl, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->universignApiUrl = $universignApiUrl;
        $this->apiKey = $apiKey;
    }

    public function createSignatureRequest(string $pdfPath, string $clientEmail, string $clientName): ?string
    {
        // Étape 1: Créer une transaction
        $transactionResponse = $this->httpClient->request('POST', "{$this->universignApiUrl}/v1/transactions", [
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'name' => "Signature Contrat - {$clientName}",
                'language' => 'fr'
            ],
        ]);

        $transactionData = $transactionResponse->toArray();
        $transactionId = $transactionData['id'] ?? null;

        if (!$transactionId) {
            throw new \RuntimeException("Impossible de créer une transaction Universign.");
        }

        // Étape 2: Télécharger le fichier
        $fileResponse = $this->httpClient->request('POST', "{$this->universignApiUrl}/v1/files", [
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
            ],
            'body' => [
                'file' => fopen($pdfPath, 'r'),
            ],
        ]);

        $fileData = $fileResponse->toArray();
        $fileId = $fileData['id'] ?? null;

        if (!$fileId) {
            throw new \RuntimeException("Impossible d'envoyer le fichier à Universign.");
        }

        // Étape 3: Ajouter le document à la transaction
        $this->httpClient->request('POST', "{$this->universignApiUrl}/v1/transactions/{$transactionId}/documents", [
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'document' => $fileId,
            ],
        ]);

        // Étape 4: Ajouter un champ de signature
        $documentResponse = $this->httpClient->request('GET', "{$this->universignApiUrl}/v1/transactions/{$transactionId}");
        $documentData = $documentResponse->toArray();
        $documentId = $documentData['documents'][0]['id'] ?? null;

        if (!$documentId) {
            throw new \RuntimeException("Impossible de récupérer l'ID du document.");
        }

        $this->httpClient->request('POST', "{$this->universignApiUrl}/v1/transactions/{$transactionId}/documents/{$documentId}/fields", [
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'type' => 'signature',
            ],
        ]);

        // Étape 5: Associer le signataire
        $this->httpClient->request('POST', "{$this->universignApiUrl}/v1/transactions/{$transactionId}/signatures", [
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'signer' => $clientEmail,
            ],
        ]);

        // Étape 6: Activer la notification du signataire
        $this->httpClient->request('POST', "{$this->universignApiUrl}/v1/transactions/{$transactionId}/participants", [
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'email' => $clientEmail,
                'schedule' => '[0]',
            ],
        ]);

        // Étape 7: Démarrer la transaction
        $this->httpClient->request('POST', "{$this->universignApiUrl}/v1/transactions/{$transactionId}/start", [
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
            ],
        ]);

        return $transactionId;
    }
}