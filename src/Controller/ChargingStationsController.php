<?php

namespace App\Controller;

use App\Entity\ChargingStations;
use App\Entity\Manufacturer;
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

        return $this->render('charging_stations/index.html.twig', [
            'chargingStations' => $chargingStations,
            'form' => $form->createView(),
            'manufacturerForm' => $manufacturerForm->createView()
        ]);
    }

    #[Route('/charging/stations/{slug}', name: 'charging_station_show', methods: ['GET'])]
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
            $imageFile = $request->files->get('image');
            if ($imageFile instanceof UploadedFile) {
                $imageFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move($this->uploadsDirectory, $imageFilename);
                    $station->setImage($imageFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $docFile = $request->files->get('documentation');
            if ($docFile instanceof UploadedFile) {
                $docFilename = uniqid() . '.' . $docFile->guessExtension();
                try {
                    $docFile->move($this->uploadsDirectory, $docFilename);
                    $station->setDocumentation($docFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de la documentation.');
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
            // Gestion de l'upload du logo fabricant
            $imageFile = $form->get('image')->getData(); // Récupération du fichier
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

    #[Route('/charging/stations/{slug}/edit', name: 'charging_station_edit', methods: ['POST'])]
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

    #[Route('/charging/stations/{slug}/delete', name: 'charging_station_delete', methods: ['POST'])]
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
}
