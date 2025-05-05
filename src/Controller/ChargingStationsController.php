<?php

namespace App\Controller;

use App\Entity\ChargingStationDocumentation;
use App\Entity\ChargingStations;
use App\Entity\Manufacturer;
use App\Form\ChargingStationDocumentationCollectionType;
use App\Form\ChargingStationsDocumentationType;
use App\Form\ChargingStationType;
use App\Form\ManufacturerType;
use App\Repository\ChargingStationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ChargingStationsController extends AbstractController
{
    private string $uploadsDirectory;

    public function __construct(ParameterBagInterface $params)
    {
        $this->uploadsDirectory = $params->get('uploads_directory');
    }

    #[Route('/borne-de-recharge', name: 'app_charging_stations')]
    public function index(ChargingStationsRepository $chargingStationsRepository, EntityManagerInterface $entityManager): Response
    {
        $chargingStations = $chargingStationsRepository->findAll();
        $station = new ChargingStations();
        $form = $this->createForm(ChargingStationType::class, $station);

        $manufacturer = new Manufacturer();
        $manufacturerForm = $this->createForm(ManufacturerType::class, $manufacturer);

        $docForm = $this->createForm(ChargingStationsDocumentationType::class, new ChargingStationDocumentation());

        return $this->render('charging_stations/index.html.twig', [
            'chargingStations' => $chargingStations,
            'form' => $form->createView(),
            'manufacturerForm' => $manufacturerForm->createView(),
            'docForm' => $docForm->createView(), // 👈
        ]);
    }

    #[Route('/borne-de-recharge/{slug}', name: 'charging_station_show', methods: ['GET'])]
    public function show(ChargingStations $station, EntityManagerInterface $entityManager): Response
    {
        $editForm = $this->createForm(ChargingStationType::class, $station);

        return $this->render('charging_stations/show.html.twig', [
            'station' => $station,
            'editForm' => $editForm->createView(),
        ]);
    }

    #[Route('/charging/stations/add', name: 'charging_station_add', methods: ['POST'])]
    public function addChargingStation(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $station = new ChargingStations();
        $form = $this->createForm(ChargingStationType::class, $station);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile instanceof UploadedFile) {
                $imageFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move($this->uploadsDirectory, $imageFilename);
                    $station->setImage($imageFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $station->generateSlug($slugger);

            $entityManager->persist($station);
            $entityManager->flush();

            $this->addFlash('success', 'Borne ajoutée avec succès !');
            return $this->redirectToRoute('app_charging_stations');
        }

        return $this->render('charging_stations/index.html.twig', [
            'chargingStations' => $entityManager->getRepository(ChargingStations::class)->findAll(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/manufacturer/add', name: 'manufacturer_add', methods: ['POST'])]
    public function addManufacturer(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'avez pas le droit d\'ajouter un propriétaire.');
        }

        $manufacturer = new Manufacturer();
        $form = $this->createForm(ManufacturerType::class, $manufacturer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile instanceof UploadedFile) {
                $imageFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move($this->uploadsDirectory, $imageFilename);
                    $manufacturer->setImage($imageFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image du fabricant.');
                }
            }

            $entityManager->persist($manufacturer);
            $entityManager->flush();

            $this->addFlash('success', 'Propriétaire ajouté avec succès !');
            return $this->redirectToRoute('app_charging_stations');
        }

        return $this->render('charging_stations/index.html.twig', [
            'chargingStations' => $entityManager->getRepository(ChargingStations::class)->findAll(),
            'manufacturerForm' => $form->createView(),
        ]);
    }

    #[Route('/borne-de-recharge/{slug}/edition', name: 'charging_station_edit', methods: ['POST'])]
    public function editChargingStation(Request $request, ChargingStations $station, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChargingStationType::class, $station);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image si une nouvelle image est fournie
            $imageFile = $request->files->get('image');
            if ($imageFile) {
                $imageFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->uploadsDirectory, $imageFilename);
                $station->setImage($imageFilename);
            }

            $entityManager->persist($station);
            $entityManager->flush();

            $this->addFlash('success', 'Borne modifiée avec succès !');
            return $this->redirectToRoute('charging_station_show', ['slug' => $station->getSlug()]);
        }

        return $this->render('charging_stations/show.html.twig', [
            'station' => $station,
            'editForm' => $form->createView(),
        ]);
    }

    #[Route('/borne-de-recharge/{slug}/delete', name: 'charging_station_delete', methods: ['POST'])]
    public function deleteChargingStation(ChargingStations $station, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'avez pas le droit de supprimer cette borne.');
        }

        $entityManager->remove($station);
        $entityManager->flush();

        $this->addFlash('success', 'Borne supprimée avec succès !');

        return $this->redirectToRoute('app_charging_stations');
    }

    #[Route('/borne-de-recharge/{slug}/documentations/ajout', name: 'charging_station_add_multiple_docs', methods: ['GET', 'POST'])]
    public function addMultipleDocs(
        Request $request,
        ChargingStations $station,
        EntityManagerInterface $em,
        ParameterBagInterface $params
    ): Response {
        $form = $this->createForm(ChargingStationDocumentationCollectionType::class);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ChargingStationDocumentation $doc */
            foreach ($form->get('docs') as $docForm) {
                $doc = $docForm->getData();
        
                $file = $docForm->get('image')->getData();
        
                if ($file) {
                    $filename = uniqid().'.'.$file->guessExtension();
                    $file->move($params->get('uploads_documentation'), $filename);
                    $doc->setImage($filename);
                }
        
                $doc->setChargingStation($station);
                $em->persist($doc);
            }
        
            $em->flush();
        
            $this->addFlash('success', 'Documentations ajoutées avec succès !');
            return $this->redirectToRoute('charging_station_show', ['slug' => $station->getSlug()]);
        }
        
    
        return $this->render('charging_stations/add_multiple_docs.html.twig', [
            'form' => $form->createView(),
            'station' => $station,
        ]);
    }

    #[Route('/documentation/{id}/edition', name: 'charging_station_doc_edit')]
public function edit(ChargingStationDocumentation $doc, Request $request, EntityManagerInterface $em, ParameterBagInterface $params): Response
{
    $form = $this->createForm(ChargingStationsDocumentationType::class, $doc);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $file = $request->files->get('charging_station_documentation')['image'] ?? null;

        if ($file) {
            $filename = uniqid() . '.' . $file->guessExtension();
            $file->move($params->get('uploads_directory'), $filename);
            $doc->setImage($filename);
        }

        $em->flush();
        $this->addFlash('success', 'Documentation modifiée avec succès');
        return $this->redirectToRoute('charging_station_show', ['slug' => $doc->getChargingStation()->getSlug()]);
    }

    return $this->render('documentation/edit.html.twig', [
        'form' => $form,
        'doc' => $doc,
    ]);
}

#[Route('/documentation/{id}/delete', name: 'charging_station_doc_delete', methods: ['POST'])]
public function delete(ChargingStationDocumentation $doc, EntityManagerInterface $em): Response
{
    $slug = $doc->getChargingStation()->getSlug();
    $em->remove($doc);
    $em->flush();

    $this->addFlash('success', 'Étape de documentation supprimée');
    return $this->redirectToRoute('charging_station_show', ['slug' => $slug]);
}

    

}
