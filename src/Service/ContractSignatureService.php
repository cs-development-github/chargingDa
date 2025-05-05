<?php

namespace App\Service;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\MailService;

class ContractSignatureService
{
    private string $universignApiUrl;
    private string $apiKey;
    private string $pdfDir;

    public function __construct(
        private HttpClientInterface $httpClient,
        private MailService $mailService,
        private EntityManagerInterface $em
    ) {
        $this->universignApiUrl = getenv('UNIVERSIGN_API_URL') ?: $_ENV['UNIVERSIGN_API_URL'] ?? throw new \RuntimeException('UNIVERSIGN_API_URL non défini');
        $this->apiKey = getenv('UNIVERSIGN_API_KEY') ?: $_ENV['UNIVERSIGN_API_KEY'] ?? throw new \RuntimeException('UNIVERSIGN_API_KEY non défini');

        $this->pdfDir = __DIR__ . '/../../public/pdf';
    }

    public function sign(Client $client): string
    {
        $pdfPath = "{$this->pdfDir}/contrat_final_{$client->getDocumentId()}.pdf";

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
                'name' => 'SignatureField1',
                'page' => 25,
                'x' => 300,
                'y' => 320,
            ]),
        ]);

        $fieldId = $signatureData->toArray()['id'] ?? null;
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
