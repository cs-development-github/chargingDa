<?php

namespace App\DTO;

use App\Entity\Client;
use App\Entity\ChargingStations;
use App\Entity\Intervention;
use App\Entity\Tarification;
use App\Entity\ChargingStationSetting;

class StationSupervisionDTO
{
    public function __construct(
        public readonly Client $client,
        public readonly ChargingStations $station,
        public readonly Intervention $intervention,
        public readonly ?Tarification $tarification,
        public readonly ?ChargingStationSetting $setting,
        public readonly string $borneName,
    ) {
        $this->chargingStationSetting = $setting;
    }

    public ?ChargingStationSetting $chargingStationSetting = null;
}