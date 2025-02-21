<?php

namespace App\Controller;

use App\Entity\Badge;
use App\Entity\Client;
use App\Entity\Intervention;
use App\Form\ClientContractFormType;
use App\Form\ClientFormType;
use App\Form\ClientInterventionFormType;
use App\Form\InterventionFormType;
use App\Service\ClientContractService;
use App\Service\MailService;
use App\Service\PdfEditorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

final class ClientController extends AbstractController
{
    #[Route('/clients', name: 'app_clients')]
    public function index(EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();
    
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir cette page.');
        }
    
        $clients = $em->getRepository(Client::class)->findByUserOrTeam($user);

        // dd($clients);
    
        return $this->render('client/index.html.twig', [
            'clients' => $clients,
        ]);
    }

    #[Route('/ajouter/client', name: 'app_add_client')]
    public function addClient(
        Request $request,
        EntityManagerInterface $entityManager,
        ClientContractService $contractService,
        MailService $mailerService,
        UrlGeneratorInterface $urlGenerator,
        Security $security
    ): Response {
        $client = new Client();
        $intervention = new Intervention();
    
        $user = $security->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour effectuer cette action.');
        }
    
        $form = $this->createForm(ClientInterventionFormType::class, [
            'client' => $client,
            'intervention' => $intervention,
        ]);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Associer l'installateur
            $intervention->setInstaller($user);
            $client->setCreatedBy($user);
    
            $entityManager->persist($client);
            $entityManager->persist($intervention);
            $entityManager->flush();
    
            if ($this->isClientDataComplete($client)) {
                $contractService->generateAndSendContract($client);
                $this->addFlash('success', 'Le client et l\'intervention ont été ajoutés et le contrat a été envoyé.');
            } else {
                $token = Uuid::v4()->toRfc4122();
                $client->setSecureToken($token);
                $entityManager->flush();
    
                $completionUrl = $urlGenerator->generate('client_complete_info', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                $mailerService->sendEmail(
                    to: $client->getEmail() ?: 'chris.vermersch@hotmail.com',
                    subject: 'Demande d\'information complémentaire contrat de supervision',
                    template: 'emails/request_document.html.twig',
                    context: ['client' => $client, 'completionUrl' => $completionUrl]
                );

                $mailerService->sendEmail(
                    to: 'contact@lodmi.com',
                    subject: 'Demande de Supervision',
                    template: 'emails/lodmi_contract.html.twig',
                    context: ['client' => $client, 'completionUrl' => $completionUrl]
                );

                $mailerService->sendEmail(
                    to: 'chris.vermersch@hotmail.com',
                    subject: 'Demande de Supervision',
                    template: 'emails/confirmation_installator.html.twig',
                    context: ['client' => $client, 'completionUrl' => $completionUrl]
                );
                
    
                $this->addFlash('info', 'Le client a été ajouté, mais certaines informations sont manquantes.');
            }
    
            return $this->redirectToRoute('app_add_client');
        }
    
        return $this->render('client/add.client.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/client/complete-info', name: 'client_complete_info')]
    public function completeClientInfo(Request $request, EntityManagerInterface $em): Response
    {
        $token = $request->query->get('token');
    
        if (!$token) {
            throw $this->createAccessDeniedException('Token manquant.');
        }
    
        // Récupération du client via le token
        $client = $em->getRepository(Client::class)->findOneBy(['secureToken' => $token]);
    
        if (!$client) {
            throw $this->createNotFoundException('Lien invalide ou client introuvable.');
        }
    
        // Récupération des bornes associées au client via la table Intervention
        $interventions = $em->getRepository(Intervention::class)->findBy(['Client' => $client]);
    
        $chargingStations = [];
        foreach ($interventions as $intervention) {
            $chargingStations[] = $intervention->getChargingStation();
        }
    
        // Création du formulaire ClientContractFormType
        $form = $this->createForm(ClientContractFormType::class, $client);
    
        return $this->render('client/complete_form.html.twig', [
            'form' => $form->createView(),
            'client' => $client,
            'chargingStations' => $chargingStations, // 🔥 On passe les bornes au template
        ]);
    }

    #[Route('/client/update-info', name: 'client_update_info', methods: ['POST'])]
    public function updateClientInfo(
        Request $request,
        EntityManagerInterface $em,
        ClientContractService $contractService
    ): Response {
        $token = $request->request->get('token');
    
        if (!$token) {
            throw $this->createAccessDeniedException('Token manquant.');
        }
    
        $client = $em->getRepository(Client::class)->findOneBy(['secureToken' => $token]);
    
        if (!$client) {
            throw $this->createNotFoundException('Client introuvable.');
        }
    
        $form = $this->createForm(ClientContractFormType::class, $client);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $client->setSecureToken(null);
            $em->persist($client);
            $em->flush();
    
            $contractService->generateAndSendContract($client);
    
            $this->addFlash('success', 'Les informations ont été mises à jour et le contrat a été envoyé.');
    
            // return $this->redirectToRoute('app_home');
        }
    
        return $this->render('client/complete_form.html.twig', [
            'form' => $form->createView(),
            'client' => $client
        ]);
    }

    /**
     * Vérifie si tous les champs obligatoires sont remplis.
     */
    private function isClientDataComplete(Client $client): bool
    {
        return !empty($client->getEmail())
            && !empty($client->getSocietyName())
            && !empty($client->getPhone())
            && !empty($client->getAdress())
            && !empty($client->getName())
            && !empty($client->getLastname())
            && !empty($client->getSiret())
            && !empty($client->getCodeNaf())
            && !empty($client->getPriceKwh())
            && !empty($client->getPriceResale())
            && !empty($client->getLegalForm());
    }
}
