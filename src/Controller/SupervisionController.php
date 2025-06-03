<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\ChargingStations;
use App\Entity\Client;
use App\Form\ConfigMixteType;
use App\Form\Step2ClientType;
use App\Form\Step4ChoiceType;
use App\Form\ConfigFlotteType;
use App\Form\ConfigPubliqueType;
use App\Entity\ChargingStationSetting;
use App\Entity\Manufacturer;
use App\Entity\Tarification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\Step5SettingsCollectionType as FormStep5SettingsCollectionType;
use Symfony\Component\HttpFoundation\Response;

class SupervisionController extends AbstractController
{
    #[Route('/supervision/step/{step}/{token}', name: 'supervision_step')]
    public function step(int $step, Request $request, EntityManagerInterface $em, ?string $token = null)
    {
        if ($step === 1) {
            if ($token) {
                /** @var ClientRepository $clientRepo */
                $clientRepo = $em->getRepository(Client::class);
                $client = $clientRepo->findOneWithAddressByToken($token);

                if (!$client) {
                    throw $this->createNotFoundException('Client introuvable pour ce token.');
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
            ]);
        }

        if ($step === 4 && !$request->query->has('config')) {
            $choiceForm = $this->createForm(Step4ChoiceType::class);
            $choiceForm->handleRequest($request);

            if ($choiceForm->isSubmitted() && $choiceForm->isValid()) {
                $selected = $choiceForm->getData()['type'];
                return $this->redirectToRoute('supervision_step', ['step' => 4, 'config' => $selected]);
            }

            return $this->render('supervision/step4_choice.html.twig', [
                'form' => $choiceForm->createView(),
                'currentStep' => 4,
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

            $form = $this->createForm($types[$configType]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $request->getSession()->set('supervision_step_4', $form->getData());

                /** @var Client $clientSession */
                $clientSession = $request->getSession()->get('supervision_step_2');
                $client = $em->getRepository(Client::class)->find($clientSession->getId());

                if ($configType === 'flotte') {

                    $client = $em->getRepository(Client::class)->find($clientSession->getId());

                    foreach ($client->getInterventions() as $intervention) {
                        $stationSession = $intervention->getChargingStation();

                        if ($stationSession) {
                            $station = $em->getRepository(ChargingStations::class)->find($stationSession->getId());

                            $tarif = new Tarification();
                            $tarif->setClient($client);
                            $tarif->setChargingStation($station);
                            $tarif->setOfferType('flotte');

                            $em->persist($tarif);
                        }
                    }

                    $em->flush();
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
                    $em->flush();
                }

                if ($configType === 'publique') {
                    $data = $form->getData();

                    foreach ($client->getInterventions() as $intervention) {
                        $station = $em->getRepository(ChargingStations::class)->find($intervention->getChargingStation()->getId());

                        $tarif = new Tarification();
                        $tarif->setClient($client);
                        $tarif->setChargingStation($station);
                        $tarif->setOfferType('publique');
                        $tarif->setReducedPrice((string) $data['prix_collab']);
                        $tarif->setPublicPrice((string) $data['prix_public']);
                        $tarif->setRechargeTimeResale((string) $data['cout_minute']);
                        $tarif->setParkingTimeResale((string) $data['penalite']);

                        $em->persist($tarif);
                    }
                    $em->flush();
                }

                return $this->redirectToRoute('supervision_step', ['step' => 5]);
            }

            $client = $request->getSession()->get('supervision_step_2');
            $totalConnectors = 0;

            if ($client instanceof Client) {
                foreach ($client->getInterventions() as $intervention) {
                    $station = $intervention->getChargingStation();
                    if ($station && is_numeric($station->getConnectors())) {
                        $totalConnectors += (int) $station->getConnectors();
                    }
                }
            }

            return $this->render("supervision/step4_{$configType}.html.twig", [
                'form' => $form->createView(),
                'totalConnectors' => $totalConnectors,
                'currentStep' => 4,
            ]);
        }

        if ($step === 5) {

            if (!$token) {
                $token = $request->get('token') ?? $request->query->get('token');
            }
            $session = $request->getSession();
            $client = $session->get('supervision_step_2');

            if (!$client instanceof Client) {
                throw $this->createNotFoundException('Client introuvable en session.');
            }

            $settings = [];
            foreach ($client->getInterventions() as $intervention) {
                $station = $intervention->getChargingStation();
                if ($station) {
                    $setting = new ChargingStationSetting();
                    $setting->setChargingStation($station);
                    $settings[] = $setting;
                }
            }

            $data = ['settings' => $settings];
            $form = $this->createForm(FormStep5SettingsCollectionType::class, $data);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $submittedSettings = $form->get('settings')->getData();

                /** @var Client|null $client */
                $client = $session->get('supervision_step_2');
                $config = $session->get('supervision_step_4');

                if (!$client || !$config) {
                    $this->addFlash('error', 'Des données sont manquantes pour finaliser.');
                    return $this->redirectToRoute('supervision_step', ['step' => 1, 'token' => $token]);
                }

                $address = $client->getAddress();
                if ($address instanceof Address && !$em->contains($address)) {
                    $em->persist($address); // important pour éviter les erreurs
                }

                foreach ($client->getInterventions() as $intervention) {
                    $em->persist($intervention);
                }

                foreach ($submittedSettings as $setting) {
                    $station = $setting->getChargingStation();

                    if ($station !== null && !$em->contains($station)) {
                        $station = $em->getRepository(ChargingStations::class)->find($station->getId());
                        $setting->setChargingStation($station);
                    }

                    if ($station && $station->getManufacturer() && !$em->contains($station->getManufacturer())) {
                        $manufacturer = $em->getRepository(Manufacturer::class)->find($station->getManufacturer()->getId());
                        $station->setManufacturer($manufacturer);
                    }

                    $em->persist($setting);
                }

                $em->persist($client); // persist le client ici, après l’adresse
                $em->persist($config);

                $em->flush();
                $session->clear();

                $this->addFlash('success', 'Supervision enregistrée avec succès.');

                return $this->redirectToRoute('homepage');
            }

            return $this->render('supervision/step5_settings.html.twig', [
                'form' => $form->createView(),
                'currentStep' => 5,
                'token' => $token,
            ]);
        }
        throw $this->createNotFoundException('Étape non gérée.');
    }
}
