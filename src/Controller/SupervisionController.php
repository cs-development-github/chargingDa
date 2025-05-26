<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\ChargingStationSetting;
use App\Entity\Client;
use App\Form\ConfigFlotteType;
use App\Form\ConfigMixteType;
use App\Form\ConfigPubliqueType;
use App\Form\Step2ClientType;
use App\Form\Step4ChoiceType;
use App\Form\Step5SettingsCollectionType as FormStep5SettingsCollectionType;
use Doctrine\ORM\EntityManagerInterface;
use Step5SettingsCollectionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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

            return $this->render('supervision/step1_warning.html.twig');
        }

        if ($step === 2) {
            $client = $request->getSession()->get('supervision_step_2');
            if (!$client instanceof Client) {
                throw $this->createNotFoundException('Client introuvable en session.');
            }

            $form = $this->createForm(Step2ClientType::class, $client);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $request->getSession()->set('supervision_step_2', $form->getData());
                return $this->redirectToRoute('supervision_step', ['step' => 3]);
            }

            return $this->render('supervision/step2_client.html.twig', [
                'form' => $form->createView(),
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
            ]);
        }

        if ($step === 5) {
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

            $form = $this->createForm(FormStep5SettingsCollectionType::class, []);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $submitted = $form->get('settings')->getData();

                foreach ($submitted as $setting) {
                    $em->persist($setting);
                }

                $em->flush();

                $session->clear();
                $this->addFlash('success', 'Supervision enregistrée avec succès !');
                return $this->redirectToRoute('homepage');
            }

            return $this->render('supervision/step5_settings.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        throw $this->createNotFoundException('Étape non gérée.');
    }
}
