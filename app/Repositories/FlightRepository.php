<?php

namespace App\Repositories;

use App\Interface\FlightRepositoryInterface;
use App\Models\Flight;

class FlightRepository implements FlightRepositoryInterface
{
    public function getAllFlight($filter = null)
    {
        $query = Flight::with(['airline', 'segments.airport', 'classes']);

        if ($filter) {
            if (!empty($filter['origin'])) {
                $query->whereHas('segments', function ($q) use ($filter) {
                    $q->where('sequence', 1)
                        ->whereHas('airport', function ($q2) use ($filter) {
                            $q2->where('iata_code', $filter['origin']);
                        });
                });
            }

            if (!empty($filter['destination'])) {
                $query->whereHas('segments', function ($q) use ($filter) {
                    $q->whereHas('airport', function ($q2) use ($filter) {
                        $q2->where('iata_code', $filter['destination']);
                    });
                });
            }

            if (!empty($filter['departure_date'])) {
                $query->whereHas('segments', function ($q) use ($filter) {
                    $q->where('sequence', 1)
                        ->whereDate('time', $filter['departure_date']);
                });
            }

            if (!empty($filter['class_type'])) {
                $query->whereHas('classes', function ($q) use ($filter) {
                    $q->where('class_type', $filter['class_type']);
                });
            }
        }

        return $query->get();
    }

    public function getFlighhtByFlightNumber($FlightNumber)
    {
        return Flight::with(['airline', 'segments.airport', 'classes', 'seats'])
            ->where('flight_number', $FlightNumber)
            ->first();
    }
}