<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Tarification;
use App\Form\ConfigMixteType;
use App\Form\Step2ClientType;
use App\Form\Step4ChoiceType;
use App\Form\ConfigFlotteType;
use App\Form\ConfigPubliqueType;
use App\Entity\ChargingStations;
use App\Entity\ChargingStationSetting;
use App\Service\ClientContractService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\Step5SettingsCollectionType as FormStep5SettingsCollectionType;

class SupervisionController extends AbstractController
{
    #[Route('/supervision/step/{step}/{token}', name: 'supervision_step')]
    public function step(int $step, Request $request, EntityManagerInterface $em, ?string $token = null, ClientContractService $contractService)
    {
        if ($step === 1) {
            if ($token) {
                /** @var ClientRepository $clientRepo */
                $clientRepo = $em->getRepository(Client::class);
                $client = $clientRepo->findOneWithAddressByToken($token);

                if (!$client) {
                    return $this->render('supervision/_client_not_found.html.twig', [
                        'currentStep' => 1,
                    ]);
                }

                if (!$token) {
                    return $this->render('supervision/_token_not_found.html.twig', [
                        'currentStep' => 1,
                    ]);
                }

                $request->getSession()->set('supervision_step_2', $client);
            }

            return $this->render('supervision/step1_warning.html.twig', [
                'currentStep' => 1,
                'token' => $token
            ]);
        }

        if ($step === 2) {

            $session = $request->getSession();
            $token = $token ?? $request->get('token') ?? $request->query->get('token');

            if (!$token) {
                throw $this->createAccessDeniedException('Token manquant.');
            }

            $client = $em->getRepository(Client::class)->findOneBy(['secureToken' => $token]);

            if (!$client) {
                throw $this->createNotFoundException('Client introuvable pour ce token.');
            }

            $form = $this->createForm(Step2ClientType::class, $client);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $createdBy = $client->getCreatedBy();
                if ($createdBy && !$em->contains($createdBy)) {
                    $createdBy = $em->getRepository(User::class)->find($createdBy->getId());
                    $client->setCreatedBy($createdBy);
                }

                $address = $client->getAddress();
                if ($address !== null && $address->getId() === null) {
                    $em->persist($address);
                }

                $em->flush();

                $session->set('supervision_step_2_id', $client->getId());

                return $this->redirectToRoute('supervision_step', ['step' => 3, 'token' => $token]);
            }

            return $this->render('supervision/step2_client.html.twig', [
                'form' => $form->createView(),
                'currentStep' => 2,
            ]);
        }

        if ($step === 3) {
            $data = $request->getSession()->get('supervision_step_2');
            if (!$data instanceof Client) {
                throw $this->createNotFoundException('Client manquant en session.');
            }

            $interventions = $data->getInterventions();

            return $this->render('supervision/step3_recap_stations.html.twig', [
                'interventions' => $interventions,
                'currentStep' => 3,
                'token' => $data->getSecureToken(),
            ]);
        }


        if ($step === 4 && !$request->query->has('config')) {
            $choiceForm = $this->createForm(Step4ChoiceType::class);
            $choiceForm->handleRequest($request);

            if ($choiceForm->isSubmitted() && $choiceForm->isValid()) {
                $selected = $choiceForm->getData()['type'];
                return $this->redirectToRoute('supervision_step', ['step' => 4, 'config' => $selected, 'token' => $token]);
            }

            return $this->render('supervision/step4_choice.html.twig', [
                'form' => $choiceForm->createView(),
                'currentStep' => 4,
                'token' => $token
            ]);
        }

        if ($step === 4 && $request->query->has('config')) {

            $configType = $request->query->get('config');

            $types = [
                'flotte' => ConfigFlotteType::class,
                'publique' => ConfigPubliqueType::class,
                'mixte' => ConfigMixteType::class,
            ];

            if (!isset($types[$configType])) {
                throw $this->createNotFoundException("Type de configuration invalide");
            }

            // Récupération client + calcul AC/DC
            $client = $request->getSession()->get('supervision_step_2');
            $totalConnectors = 0;
            $acCount = 0;
            $dcCount = 0;

            if ($client instanceof Client) {
                foreach ($client->getInterventions() as $intervention) {
                    $station = $intervention->getChargingStation();
                    if ($station && is_numeric($station->getConnectors())) {
                        $totalConnectors += (int) $station->getConnectors();
                    }

                    if ($station) {
                        $type = strtoupper($station->getType());
                        if ($type === 'AC') {
                            $acCount++;
                        } elseif ($type === 'DC') {
                            $dcCount++;
                        } elseif ($type === 'ACDC') {
                            $acCount++;
                            $dcCount++;
                        }
                    }
                }
            }

            // Construction du formulaire avec les bons paramètres
            $formOptions = [];
            if ($configType === 'mixte') {
                $formOptions = [
                    'ac_count' => $acCount,
                    'dc_count' => $dcCount,
                ];
            }

            $form = $this->createForm($types[$configType], null, $formOptions);
            $form->handleRequest($request);

            // Traitement à la soumission
            if ($form->isSubmitted() && $form->isValid()) {
                $request->getSession()->set('supervision_step_4', $form->getData());

                $client = $em->getRepository(Client::class)->find($client->getId());

                if ($configType === 'flotte') {
                    foreach ($client->getInterventions() as $intervention) {
                        $station = $intervention->getChargingStation();
                        if ($station) {
                            $station = $em->getRepository(ChargingStations::class)->find($station->getId());
                            $tarif = new Tarification();
                            $tarif->setClient($client);
                            $tarif->setChargingStation($station);
                            $tarif->setOfferType('flotte');
                            $em->persist($tarif);
                        }
                    }
                }

                if ($configType === 'mixte') {
                    $data = $form->getData();
                    foreach ($client->getInterventions() as $intervention) {
                        $station = $em->getRepository(ChargingStations::class)->find($intervention->getChargingStation()->getId());
                        $tarif = new Tarification();
                        $tarif->setClient($client);
                        $tarif->setChargingStation($station);
                        $tarif->setOfferType('mixte');
                        $tarif->setReducedPrice((string) $data['prix_collab']);
                        $tarif->setPublicPrice((string) $data['prix_public']);
                        $tarif->setRechargeTimeResale((string) $data['cout_minute']);
                        $tarif->setParkingTimeResale((string) $data['penalite']);
                        $em->persist($tarif);
                    }
                }

                if ($configType === 'publique') {
                    $prixCollab = $form->get('prix_collab')->getData();
                    $prixPublic = $form->get('prix_public')->getData();
                    $coutMinute = $form->get('cout_minute')->getData();
                    $penalite   = $form->get('penalite')->getData();

                    foreach ($client->getInterventions() as $intervention) {
                        $station = $em->getRepository(ChargingStations::class)->find($intervention->getChargingStation()->getId());
                        $tarif = new Tarification();
                        $tarif->setClient($client);
                        $tarif->setChargingStation($station);
                        $tarif->setOfferType('publique');
                        $tarif->setReducedPrice((string) $prixCollab);
                        $tarif->setPublicPrice((string) $prixPublic);
                        $tarif->setRechargeTimeResale((string) $coutMinute);
                        $tarif->setParkingTimeResale((string) $penalite);
                        $em->persist($tarif);
                    }
                }

                $em->flush();

                return $this->redirectToRoute('supervision_step', [
                    'step' => 5,
                    'token' => $token,
                ]);
            }

            return $this->render("supervision/step4_{$configType}.html.twig", [
                'form' => $form->createView(),
                'totalConnectors' => $totalConnectors / 2,
                'currentStep' => 4,
                'token' => $client instanceof Client ? $client->getSecureToken() : null,
                'acCount' => $acCount,
                'dcCount' => $dcCount,
            ]);
        }

        if ($step === 5) {
            $sessionClient = $request->getSession()->get('supervision_step_2');
            if (!$sessionClient instanceof Client) {
                throw $this->createNotFoundException('Client invalide ou absent en session.');
            }

            $client = $em->getRepository(Client::class)->find($sessionClient->getId());
            if (!$client) {
                throw $this->createNotFoundException('Client introuvable en base.');
            }

            $createdBy = $client->getCreatedBy();
            if ($createdBy && !$em->contains($createdBy)) {
                $user = $em->getRepository(User::class)->find($createdBy->getId());
                $client->setCreatedBy($user);
            }

            $chargingStations = [];
            foreach ($client->getInterventions() as $intervention) {
                $station = $intervention->getChargingStation();
                if ($station && $station->getId()) {
                    $stationRefreshed = $em->getRepository(ChargingStations::class)->find($station->getId());
                    if ($stationRefreshed) {
                        $chargingStations[] = $stationRefreshed;
                    }
                }
            }

            if (empty($chargingStations)) {
                throw $this->createNotFoundException('Aucune borne trouvée pour ce client.');
            }

            $settings = [];
            foreach ($chargingStations as $station) {
                $setting = new ChargingStationSetting();
                $setting->setChargingStation($station);
                $setting->setClient($client);
                $settings[] = $setting;
            }

            $form = $this->createForm(FormStep5SettingsCollectionType::class, ['settings' => $settings]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                foreach ($form->getData()['settings'] as $setting) {
                    $em->persist($setting);
                }

                $em->flush();

                $contractService->generateAndSendContract($client);

                return $this->redirectToRoute('client_success_page', ['token' => $client->getSecureToken()]);
            }

            return $this->render('supervision/step5_settings.html.twig', [
                'form' => $form->createView(),
                'chargingStations' => $chargingStations,
                'currentStep' => 5,
                'token' => $token
            ]);
        }

        throw $this->createNotFoundException('Étape non gérée.');
    }
}
