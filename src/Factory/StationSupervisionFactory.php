<?php

// src/Factory/StationSupervisionFactory.php
namespace App\Factory;

use App\Entity\Intervention;
use App\Repository\TarificationRepository;
use App\Repository\ChargingStationSettingRepository;
use App\DTO\StationSupervisionDTO;

class StationSupervisionFactory
{
    public function __construct(
        private TarificationRepository $tarificationRepo,
        private ChargingStationSettingRepository $settingRepo
    ) {}

    /**
     * @param Intervention[] $interventions
     * @return StationSupervisionDTO[]
     */
    public function createFromInterventions(array $interventions): array
    {
        $uniqueStations = [];
    
        foreach ($interventions as $intervention) {
            $station = $intervention->getChargingStation();
            $stationId = $station->getId();
    
            if (isset($uniqueStations[$stationId])) {
                continue;
            }
    
            $tarification = $this->tarificationRepo->findOneBy([
                'chargingStation' => $station,
                'client' => $intervention->getClient()
            ]);            
            $setting = $this->settingRepo->findOneBy(['chargingStation' => $station]);
    
            $uniqueStations[$stationId] = new StationSupervisionDTO(
                client: $intervention->getClient(),
                station: $station,
                intervention: $intervention,
                tarification: $tarification,
                setting: $setting,
                borneName: $intervention->getBorneName()
            );
            
        }
    
        return array_values($uniqueStations);
    }
}