<?php
namespace App\Service;

use App\Entity\Client;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;

class UniversignService
{
    private string $universignApiUrl;
    private string $apiKey;
    private HttpClientInterface $httpClient;

    public function __construct(
        HttpClientInterface $httpClient, string $universignApiUrl, string $apiKey,
        private MailService $mailService,
        private EntityManagerInterface $em,
        private string $projectDir
        )
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

    public function sign(Client $client): string
    {
        $pdfPath = $this->projectDir . "/public/pdf/contrat_{$client->getDocumentId()}.pdf";

        if (!file_exists($pdfPath)) {
            throw new \RuntimeException("Le fichier PDF du contrat est introuvable.");
        }

        // 1️⃣ Créer la transaction
        $transactionResponse = $this->httpClient->request('POST', "{$this->universignApiUrl}/v1/transactions", [
            'auth_basic' => [$this->apiKey, ''],
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'name' => "Signature Contrat - {$client->getName()}",
                'language' => 'fr',
            ],
        ]);

        $transactionId = $transactionResponse->toArray()['id'] ?? null;
        if (!$transactionId) throw new \RuntimeException("Échec de création de la transaction Universign.");

        sleep(2);

        // 2️⃣ Envoyer le fichier PDF
        $fileResponse = $this->httpClient->request('POST', "{$this->universignApiUrl}/v1/files", [
            'auth_basic' => [$this->apiKey, ''],
            'body' => ['file' => fopen($pdfPath, 'r')],
        ]);

        $fileId = $fileResponse->toArray()['id'] ?? null;
        if (!$fileId) throw new \RuntimeException("Échec de l'envoi du fichier.");

        // 3️⃣ Ajouter le fichier à la transaction
        $this->httpClient->request('POST', "{$this->universignApiUrl}/v1/transactions/{$transactionId}/documents", [
            'auth_basic' => [$this->apiKey, ''],
            'body' => ['document' => $fileId],
        ]);

        sleep(2);

        // 4️⃣ Récupérer le document ID
        $documentData = $this->httpClient->request('GET', "{$this->universignApiUrl}/v1/transactions/{$transactionId}", [
            'auth_basic' => [$this->apiKey, ''],
        ])->toArray();

        $documentId = $documentData['documents'][0]['id'] ?? null;
        if (!$documentId) throw new \RuntimeException("Document non trouvé.");

        // 5️⃣ Ajouter le champ de signature
        $signatureData = $this->httpClient->request('POST', "{$this->universignApiUrl}/v1/transactions/{$transactionId}/documents/{$documentId}/fields", [
            'auth_basic' => [$this->apiKey, ''],
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => http_build_query([
                'type' => 'signature',
                'position[page]' => 2,
                'position[x]' => 120,
                'position[y]' => 170,
                'position[width]' => 200,
                'position[height]' => 50,
            ]),
        ])->toArray();

        $fieldId = $signatureData['id'] ?? null;
        if (!$fieldId) throw new \RuntimeException("Champ de signature non ajouté.");

        // 6️⃣ Ajouter le signataire
        $this->httpClient->request('POST', "{$this->universignApiUrl}/v1/transactions/{$transactionId}/signatures", [
            'auth_basic' => [$this->apiKey, ''],
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => http_build_query([
                'signer' => $client->getEmail(),
                'field'  => $fieldId,
            ]),
        ]);

        // 7️⃣ Démarrer la transaction
        $this->httpClient->request('POST', "{$this->universignApiUrl}/v1/transactions/{$transactionId}/start", [
            'auth_basic' => [$this->apiKey, ''],
        ]);

        sleep(2);

        // 8️⃣ Récupérer l’URL de signature
        $finalTransaction = $this->httpClient->request('GET', "{$this->universignApiUrl}/v1/transactions/{$transactionId}", [
            'auth_basic' => [$this->apiKey, ''],
        ])->toArray();

        $signatureUrl = $finalTransaction['actions'][0]['url'] ?? null;
        if (!$signatureUrl) throw new \RuntimeException("Lien de signature non trouvé.");

        // 9️⃣ Envoi de l'e-mail avec le lien
        $this->mailService->sendEmail(
            to: $client->getEmail(),
            subject: "Signature électronique de votre contrat",
            template: 'emails/sign_contract.html.twig',
            context: [
                'client' => $client,
                'signatureUrl' => $signatureUrl,
            ]
        );

        // 🔟 Mise à jour client
        $client->setSignatureTransactionId($transactionId);
        $this->em->persist($client);
        $this->em->flush();

        return $signatureUrl;
    }
}
