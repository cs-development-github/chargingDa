<?php

namespace App\Service;

use App\DTO\StationSupervisionDTO;
use App\Entity\ChargingStations;
use App\Entity\Client;
use App\Entity\ChargingStationSetting;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class ClientSupervisionDataService
{
    private string $externalApiToken = '4565adcf6ff98a5bb7d7862a91c553d0b2e5e35e096f0a02e15aa8399cfa6288f23ffa09133af056c1e8776e250ee12b9c078fd9a82255193d19dcc4abce1c05aef8e25e64c453526e5ff2f835af24719130568f6a43cd21dac17e7e3396d8e90530b0b40828cb1b66ab639e75d012a1b2d27019d4a5461ac08beaf6b9d2ed8b'; // Token tronqué pour sécurité
    private ?string $partnerToken = null;

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger
    ) {}

    public function superviseClientStations(Client $client, array $dtos): array
    {
        $steps = [];
    
        try {
            $this->createUser($client);
            $steps[] = ['label' => 'Création de l\'utilisateur', 'status' => true];
        } catch (\Throwable $e) {
            $steps[] = ['label' => 'Erreur création utilisateur', 'status' => false, 'message' => $e->getMessage()];
        }
    
        try {
            $this->authenticatePartner();
            $steps[] = ['label' => 'Authentification', 'status' => true];
        } catch (\Throwable $e) {
            $steps[] = ['label' => 'Erreur auth partenaire', 'status' => false, 'message' => $e->getMessage()];
            return $steps;
        }
    
        try {
            $firstDto = $dtos[0];
            $setting = $firstDto->chargingStationSetting;
    
            $companyId = $this->createOrGetCompany($client);
            $steps[] = ['label' => 'Création de la société', 'status' => true];
    
            $siteId = $this->createOrGetSite($client, $setting, $companyId);
            $steps[] = ['label' => 'Création du site', 'status' => true];
    
            $siteAreaId = $this->createOrGetSiteArea($client, $setting, $siteId);
            $steps[] = ['label' => 'Création de la zone', 'status' => true];
    
            foreach ($dtos as $dto) {
                try {
                    $this->createPricingDefinition($dto);
                    $steps[] = ['label' => "Affectation des tarifs {$dto->borneName}", 'status' => true];
                } catch (\Throwable $e) {
                    $steps[] = ['label' => "Erreur tarif {$dto->borneName}", 'status' => false, 'message' => $e->getMessage()];
                }
    
                try {
                    $this->assignSiteAreaToStation($dto, $siteAreaId, $dto->station);
                    $steps[] = ['label' => "Assignation de la borne : {$dto->borneName}", 'status' => true];
                } catch (\Throwable $e) {
                    $steps[] = ['label' => "Erreur assignation {$dto->borneName}", 'status' => false, 'message' => $e->getMessage()];
                }
            }
    
        } catch (\Throwable $e) {
            $steps[] = ['label' => 'Erreur globale', 'status' => false, 'message' => $e->getMessage()];
        }
    
        return $steps;
    }

    private function createUser(Client $client): void
    {
        $email = $client->getEmail();
        $url = 'https://lodmi.charge-angels.com/v1/api/users?Search=' . urlencode($email) . '&Skip=0&Limit=100&SortFields=id&OnlyRecordCount=false&WithAuth=true';
    
        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->externalApiToken,
                    'Accept' => 'application/json',
                ],
            ]);
    
            if ($response->getStatusCode() >= 300) {
                $this->logger->warning("[Supervision] ⚠️ Erreur lors de la vérification de l'existence de l'utilisateur $email");
                return;
            }
    
            $data = $response->toArray();
            foreach ($data['result'] ?? [] as $user) {
                if (strcasecmp($user['email'], $email) === 0) {
                    $this->logger->info("[Supervision] ✅ Utilisateur déjà existant : $email");
                    return;
                }
            }
        } catch (\Throwable $e) {
            $this->logger->error("[Supervision] ❌ Exception lors de la vérification de l'utilisateur $email : " . $e->getMessage());
            return;
        }
    
        $urlCreate = 'https://srv.mobi-gest.com:5059/create_user';
        $payload = [
            'email' => $client->getEmail(),
            'lastName' => $client->getLastName(),
            'name' => $client->getName(),
            'password' => 'MotDePasse123.',
            'phone' => $client->getPhone() ?? '0600000000'
        ];
    
        try {
            $this->makePostRequest($urlCreate, $payload, $this->externalApiToken, '[Supervision] 🆕 Utilisateur créé');
        } catch (\Throwable $e) {
            $this->logger->error("[Supervision] ❌ Échec de création de l'utilisateur $email : " . $e->getMessage());
        }
    }

    private function authenticatePartner(): void
    {
        $url = 'https://lodmi.charge-angels.com/v1/auth/signin';
        $payload = [
            "email" => "partenaire@partenaire.com",
            "password" => "aDTQE83e2XkBaxTU@@",
            "acceptEula" => true,
            "acceptPrivacy" => true,
            "tenant" => "lodmi"
        ];

        $response = $this->httpClient->request('POST', $url, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $payload,
        ]);

        if ($response->getStatusCode() >= 300) {
            throw new \RuntimeException('Erreur auth partenaire');
        }

        $data = $response->toArray();
        $this->partnerToken = $data['token'] ?? throw new \RuntimeException('Token manquant');
        $this->logger->info('[Supervision] Authentification partenaire réussie');
    }

    private function createOrGetCompany(Client $client): string
    {
        $name = $client->getSocietyName();
        $url = 'https://lodmi.charge-angels.com/v1/api/companies?Search=' . urlencode($name) . '&Skip=0&SortFields=id&Limit=100&WithAuth=true';

        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->partnerToken,
                'Accept' => 'application/json',
            ],
        ]);

        $data = $response->toArray();
        foreach ($data['result'] ?? [] as $company) {
            if (strcasecmp($company['name'], $name) === 0) {
                $this->logger->info("[Supervision] ✅ Company existante : {$name} (id: {$company['id']})");
                return $company['id'];
            }
        }

        $this->logger->info("[Supervision] 🆕 Company non trouvée, création : {$name}");
        return $this->createCompany($client);
    }

    private function createCompany(Client $client): string
    {
        $url = 'https://lodmi.charge-angels.com/v1/api/companies';
        $payload = [
            "name" => $client->getSocietyName(),
            "issuer" => true,
            "address" => [
                "address1" => trim($client->getAddress()->getStreetNumber() . ' ' . $client->getAddress()->getStreetName()),
                "postalCode" => $client->getAddress()->getPostalCode(),
                "city" => $client->getAddress()->getCity(),
                "department" => "default",
                "region" => "default",
                "country" => $client->getAddress()->getCountry(),
                "coordinates" => [0, 0]
            ],
            "logo" => ""
        ];

        return $this->postEntityAndReturnId($url, $payload, '[Supervision] Company créée');
    }

    private function createOrGetSite(Client $client,ChargingStationSetting $setting, string $companyId): string
    {
        $name = 'Site ' . $client->getSocietyName();
        $url = 'https://lodmi.charge-angels.com/v1/api/sites?Search=' . urlencode($name) . '&Skip=0&Limit=100&WithAuth=true';

        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->partnerToken,
                'Accept' => 'application/json',
            ],
        ]);

        $data = $response->toArray();
        foreach ($data['result'] ?? [] as $site) {
            if (strcasecmp($site['name'], $name) === 0) {
                $this->logger->info("[Supervision] ✅ Site existant : {$name} (id: {$site['id']})");
                return $site['id'];
            }
        }

        $payload = [
            "name" => $name,
            "address" => [
                "address1" => $setting->getAddressLine(),
                "postalCode" => $setting->getPostalCode(),
                "city" => $setting->getCity(),
                "department" => $setting->getDepartment() ?? "Non défini",
                "region" => $setting->getRegion() ?? "Non défini",
                "country" => $setting->getCountry(),
                "coordinates" => [
                    $setting->getLatitude() ?? 0,
                    $setting->getLongitude() ?? 0
                ]
            ],
            "public" => true,
            "autoUserSiteAssignment" => true,
            "companyID" => $companyId,
            "issuer" => true,
            "image" => ""
        ];

        return $this->postEntityAndReturnId('https://lodmi.charge-angels.com/v1/api/sites', $payload, '[Supervision] Site créé');
    }

    private function createOrGetSiteArea(Client $client,ChargingStationSetting $setting, string $siteId): string
    {
        $name = 'Zone ' . $client->getSocietyName();
        $url = 'https://lodmi.charge-angels.com/v1/api/site-areas?Search=' . urlencode($name) . '&Skip=0&Limit=100&WithAuth=true';

        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->partnerToken,
                'Accept' => 'application/json',
            ],
        ]);

        $data = $response->toArray();
        foreach ($data['result'] ?? [] as $area) {
            if (strcasecmp($area['name'], $name) === 0) {
                $this->logger->info("[Supervision] ✅ Zone existante : {$name} (id: {$area['id']})");
                return $area['id'];
            }
        }

        $payload = [
            "name" => $name,
            "address" => [
                "address1" => $setting->getAddressLine(),
                "address2" => "",
                "postalCode" => $setting->getPostalCode(),
                "city" => $setting->getCity(),
                "department" => $setting->getDepartment() ?? "Non défini",
                "region" => $setting->getRegion() ?? "Non défini",
                "country" => $setting->getCountry(),
                "coordinates" => [
                    $setting->getLatitude() ?? 0,
                    $setting->getLongitude() ?? 0
                ]
            ],
            "issuer" => true,
            "maximumPower" => 22080,
            "numberOfPhases" => 3,
            "siteID" => $siteId,
            "smartCharging" => false,
            "voltage" => "230",
            "image" => ""
        ];

        return $this->postEntityAndReturnId('https://lodmi.charge-angels.com/v1/api/site-areas', $payload, '[Supervision] Site Area créée');
    }

    private function postEntityAndReturnId(string $url, array $payload, string $logMessage): string
    {
        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->partnerToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        if ($response->getStatusCode() >= 300) {
            $error = $response->getContent(false);
            $this->logger->error("$logMessage - Échec : $error");
            throw new \RuntimeException('Erreur API lors de la création');
        }

        $data = $response->toArray();
        $id = $data['id'] ?? throw new \RuntimeException('ID manquant');
        $this->logger->info("$logMessage (id: $id)");

        return $id;
    }

    private function createPricingDefinition(StationSupervisionDTO $dto): void
    {
        $tarification = $dto->tarification;
        if (!$tarification) {
            $this->logger->warning('[Supervision] Pas de tarification pour ' . $dto->borneName);
            return;
        }

        $payload = [
            "name" => "Tarification exploitation",
            "description" => "Tarification pour " . $dto->client->getSocietyName(),
            "entityID" => $dto->borneName,
            "entityType" => "ChargingStation",
            "issuer" => true,
            "dimensions" => [
                "flatFee" => ["active" => true, "price" => $tarification->getFixedFeePublic(), "stepSize" => 0],
                "energy" => ["active" => true, "price" => $tarification->getPublicPrice(), "stepSize" => 0],
                "chargingTime" => ["active" => true, "price" => $tarification->getRechargeTimePublic(), "stepSize" => 0],
                "parkingTime" => ["active" => true, "price" => $tarification->getParkingTimePublic(), "stepSize" => 0]
            ]
        ];

        $this->makePostRequest('https://lodmi.charge-angels.com/v1/api/pricing-definitions', $payload, $this->partnerToken, '[Supervision] Tarification créée pour ' . $dto->borneName);
    }

    private function assignSiteAreaToStation(StationSupervisionDTO $dto, string $siteAreaId, ChargingStations $station): void
    {
        $url = "https://lodmi.charge-angels.com/v1/api/charging-stations/{$dto->borneName}/parameters";
        $payload = ["siteAreaID" => $siteAreaId, "issuer" => true];

        $response = $this->httpClient->request('PUT', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->partnerToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        if ($response->getStatusCode() >= 300) {
            throw new \RuntimeException("Erreur assignation siteArea à {$dto->borneName}");
        }

        $this->logger->info("[Supervision] SiteArea assigné à la borne {$dto->borneName}");
    }

    private function makePostRequest(string $url, array $payload, ?string $token, string $successMessage): void
    {
        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        if ($response->getStatusCode() >= 300) {
            $message = $response->getContent(false);
            $this->logger->error("$successMessage - Échec : $message");
            throw new \RuntimeException('Erreur API');
        }

        $this->logger->info($successMessage);
    }
}
