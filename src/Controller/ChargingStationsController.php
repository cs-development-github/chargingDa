<?php

namespace App\Controller;

use App\Entity\ChargingStations;
use App\Form\ChargingStationType;
use App\Repository\ChargingStationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ChargingStationsController extends AbstractController
{
    private string $uploadsDirectory;

    public function __construct(ParameterBagInterface $params)
    {
        $this->uploadsDirectory = $params->get('uploads_directory');
    }

    #[Route('/charging/stations', name: 'app_charging_stations')]
    public function index(ChargingStationsRepository $chargingStationsRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $chargingStations = $chargingStationsRepository->findAll();
        $station = new ChargingStations();
        $form = $this->createForm(ChargingStationType::class, $station);

        return $this->render('charging_stations/index.html.twig', [
            'chargingStations' => $chargingStations,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/charging/stations/{id}', name: 'charging_station_show', methods: ['GET'])]
    public function show(ChargingStations $station): Response
    {
        return $this->render('charging_stations/show.html.twig', [
            'station' => $station,
        ]);
    }


    #[Route('/charging/stations/add', name: 'charging_station_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $station = new ChargingStations();
        $form = $this->createForm(ChargingStationType::class, $station);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
    
            $imageFile = $request->files->get('image');
            if ($imageFile) {
                $imageFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move($this->uploadsDirectory, $imageFilename);
                $station->setImage($imageFilename);
            }
    
            $manufacturerImageFile = $request->files->get('manufacturer_image');
            if ($manufacturerImageFile) {
                $manufacturerImageFilename = uniqid().'.'.$manufacturerImageFile->guessExtension();
                $manufacturerImageFile->move($this->uploadsDirectory, $manufacturerImageFilename);
                $station->setManufacturerImage($manufacturerImageFilename);
            }
    
            $docFile = $request->files->get('documentation');
            if ($docFile) {
                $docFilename = uniqid().'.'.$docFile->guessExtension();
                $docFile->move($this->uploadsDirectory, $docFilename);
                $station->setDocumentation($docFilename);
            }
    
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
    
}

