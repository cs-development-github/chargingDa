<?php

namespace App\Form\DataTransformer;

use App\Entity\ChargingStations;
use App\Repository\ChargingStationsRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ChargingStationToIdTransformer implements DataTransformerInterface
{
    public function __construct(private ChargingStationsRepository $repository) {}

    public function transform($value): string
    {
        return $value?->getId() ?? '';
    }

    public function reverseTransform($value): ?ChargingStations
    {
        if (!$value) return null;

        $station = $this->repository->find($value);

        if (!$station) {
            throw new TransformationFailedException("La borne n'existe pas.");
        }

        return $station;
    }
}
