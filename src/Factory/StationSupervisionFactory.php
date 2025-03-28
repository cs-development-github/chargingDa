<?php
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

            $client = $intervention->getClient();

            $tarification = $this->tarificationRepo->findOneBy([
                'chargingStation' => $station,
                'client' => $client
            ]);

            $setting = $this->settingRepo->findOneBy([
                'chargingStation' => $station
            ]);

            $dto = new StationSupervisionDTO(
                client: $client,
                station: $station,
                intervention: $intervention,
                tarification: $tarification,
                setting: $setting,
                borneName: $intervention->getBorneName()
            );

            $dto->chargingStationSetting = $setting;

            $uniqueStations[$stationId] = $dto;
        }

        return array_values($uniqueStations);
    }
}