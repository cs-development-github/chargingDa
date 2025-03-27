<?php 

// src/Service/ClientSupervisionDataService.php

namespace App\Service;

use App\DTO\StationSupervisionDTO;
use App\Entity\ChargingStations;
use App\Entity\Client;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class ClientSupervisionDataService
{
    private string $externalApiToken = '...'; // token create_user
    private ?string $partnerToken = null;

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger
    ) {}

    public function superviseStation(StationSupervisionDTO $dto): void
    {
        $client = $dto->client;
        $station = $dto->station;
    
        $this->logger->info("[Supervision] → {$client->getEmail()} / Borne : {$station->getModel()}");
    
        $this->createUser($client);
        $this->authenticatePartner();
        
        $companyId = $this->createCompany($client);
        $siteId = $this->createSite($client, $companyId);
        $siteAreaId = $this->createSiteArea($client, $siteId);
    
        $this->createPricingDefinition($dto);
        $this->assignSiteAreaToStation($siteAreaId, $station);
    
        $this->logger->info("[Supervision] ✅ Terminé pour {$station->getModel()}");
    }
    
    

    private function createUser(Client $client): void
    {
        $url = 'https://srv.mobi-gest.com:5059/create_user';

        $payload = [
            'email' => $client->getEmail(),
            'lastName' => $client->getLastName(),
            'name' => $client->getName(),
            'password' => 'MotDePasse123.',
            'phone' => $client->getPhone() ?? '0600000000'
        ];

        $this->makePostRequest($url, $payload, $this->externalApiToken, '[Supervision] Utilisateur créé');
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

        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => $payload,
            ]);

            if ($response->getStatusCode() >= 300) {
                throw new \RuntimeException('Erreur auth partenaire');
            }

            $data = $response->toArray();
            $this->partnerToken = $data['token'] ?? null;

            if (!$this->partnerToken) {
                throw new \RuntimeException('Token manquant dans la réponse d’auth');
            }

            $this->logger->info('[Supervision] Authentification partenaire réussie');
        } catch (\Throwable $e) {
            $this->logger->error('[Supervision] Erreur auth partenaire : ' . $e->getMessage());
            throw $e;
        }
    }

    private function createCompany(Client $client): string
    {
        if (!$this->partnerToken) {
            throw new \RuntimeException("Token partenaire manquant");
        }
    
        $url = 'https://lodmi.charge-angels.com/v1/api/companies';
    
        $payload = [
            "name" => $client->getSocietyName(),
            "issuer" => true,
            "address" => [
                "address1" => trim($client->getAddress()->getStreetNumber() . ' ' . $client->getAddress()->getStreetName()),
                "address2" => "",
                "postalCode" => $client->getAddress()->getPostalCode(),
                "city" => $client->getAddress()->getCity(),
                "department" => "default",
                "region" => "default",
                "country" => $client->getAddress()->getCountry(),
                "coordinates" => [69, 69]
            ],
            "logo" => ""
        ];
    
        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->partnerToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);
    
            if ($response->getStatusCode() >= 300) {
                $message = $response->getContent(false);
                $this->logger->error("[Supervision] Company - Échec : $message");
                throw new \RuntimeException('Erreur API company');
            }
    
            $this->logger->info('[Supervision] Company créée');
    
            $data = $response->toArray();
            return $data['id'] ?? throw new \RuntimeException('ID company non trouvé');
    
        } catch (\Throwable $e) {
            $this->logger->critical('[Supervision] Exception company : ' . $e->getMessage());
            throw $e;
        }
    }
    

    private function makePostRequest(string $url, array $payload, ?string $token, string $successMessage): void
    {
        try {
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
        } catch (\Throwable $e) {
            $this->logger->critical("$successMessage - Exception : " . $e->getMessage());
            throw $e;
        }
    }

    private function createSite(Client $client, string $companyId): string
    {
        $url = 'https://lodmi.charge-angels.com/v1/api/sites';

        $payload = [
            "name" => "Premier lieu",
            "address" => [
                "address1" => trim($client->getAddress()->getStreetNumber() . ' ' . $client->getAddress()->getStreetName()),
                "postalCode" => $client->getAddress()->getPostalCode(),
                "city" => $client->getAddress()->getCity(),
                "department" => "Alpes Maritimes",
                "region" => "PACA",
                "country" => $client->getAddress()->getCountry(),
                "coordinates" => [43, 7]
            ],
            "public" => true,
            "autoUserSiteAssignment" => true,
            "companyID" => $companyId,
            "image" => "",
            "issuer" => true
        ];

        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->partnerToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            if ($response->getStatusCode() >= 300) {
                throw new \RuntimeException('Erreur création site');
            }

            $data = $response->toArray();
            $siteId = $data['id'] ?? throw new \RuntimeException('Site ID manquant');

            $this->logger->info('[Supervision] Site créé avec succès');
            return $siteId;
        } catch (\Throwable $e) {
            $this->logger->critical('[Supervision] Exception site : ' . $e->getMessage());
            throw $e;
        }
    }

    private function createSiteArea(Client $client, string $siteId): string
    {
        $url = 'https://lodmi.charge-angels.com/v1/api/site-areas';
    
        $payload = [
            "accessControl" => true,
            "address" => [
                "address1" => "wsh la zone 2",
                "address2" => "",
                "postalCode" => $client->getAddress()->getPostalCode(),
                "city" => $client->getAddress()->getCity(),
                "department" => "NPDC",
                "region" => "PACA",
                "country" => $client->getAddress()->getCountry(),
                "coordinates" => [43, 7]
            ],
            "issuer" => true,
            "maximumPower" => 22080,
            "name" => "bubulande",
            "numberOfPhases" => 3,
            "siteID" => $siteId,
            "smartCharging" => false,
            "voltage" => "230",
            "image" => ""
        ];
    
        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->partnerToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);
    
            if ($response->getStatusCode() >= 300) {
                throw new \RuntimeException('Erreur création site area');
            }
    
            $data = $response->toArray();
            $siteAreaId = $data['id'] ?? throw new \RuntimeException('SiteAreaID manquant');
    
            $this->logger->info('[Supervision] Site Area créée');
            return $siteAreaId;
        } catch (\Throwable $e) {
            $this->logger->critical('[Supervision] Exception site area : ' . $e->getMessage());
            throw $e;
        }
    }
    

    private function createPricingDefinition(StationSupervisionDTO $dto): void
    {

        $client = $dto->client;
        $station = $dto->station;
        $tarification = $dto->tarification;
        $entityId = $dto->borneName; // ✅ Correct
    
        if (!$tarification) {
            $this->logger->warning('[Supervision] Pas de tarification pour ' . $entityId);
            return;
        }

        $url = 'https://lodmi.charge-angels.com/v1/api/pricing-definitions';

        $tarification = $dto->tarification;

        if (!$tarification) {
            $this->logger->warning('[Supervision] Pas de tarification pour la station ' . $entityId);
            return;
        }

        $payload = [
            "name" => "Tarification exploitation",
            "description" => "Tarification établie par le client " . $client->getSocietyName(),
            "entityID" => $entityId,
            "entityType" => "ChargingStation",
            "issuer" => true,
            "dimensions" => [
                "flatFee" => [
                    "active" => true,
                    "price" => $tarification->getFixedFeePublic(),
                    "stepSize" => 0
                ],
                "energy" => [
                    "active" => true,
                    "price" => $tarification->getPublicPrice(),
                    "stepSize" => 0
                ],
                "chargingTime" => [
                    "active" => true,
                    "price" => $tarification->getRechargeTimePublic(),
                    "stepSize" => 0
                ],
                "parkingTime" => [
                    "active" => true,
                    "price" => $tarification->getParkingTimePublic(),
                    "stepSize" => 0
                ]
            ]
        ];

        $this->makePostRequest($url, $payload, $this->partnerToken, '[Supervision] Tarification créée pour ' . $entityId);
    }

    private function assignSiteAreaToStation(string $siteAreaId, ChargingStations $station): void
    {
        $stationName = $station->getModel();
        $url = "https://lodmi.charge-angels.com/v1/api/charging-stations/{$stationName}/parameters";

        $payload = [
            "siteAreaID" => $siteAreaId,
            "issuer" => true
        ];

        try {
            $response = $this->httpClient->request('PUT', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->partnerToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            if ($response->getStatusCode() >= 300) {
                throw new \RuntimeException("Erreur assignation siteArea à {$stationName}");
            }

            $this->logger->info("[Supervision] SiteArea assigné à la borne {$stationName}");
        } catch (\Throwable $e) {
            $this->logger->critical("[Supervision] Échec assignation siteArea pour {$stationName} : " . $e->getMessage());
            throw $e;
        }
    }
}